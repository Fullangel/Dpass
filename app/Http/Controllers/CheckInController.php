<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Enums\Status;
use App\Models\Visitor;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PreRegister;
use Illuminate\Support\Str;
use App\Enums\VisitorStatus;
use Illuminate\Http\Request;
use App\Models\VisitingDetails;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Services\JwtTokenService;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmployeConfirmation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Services\PushNotificationService;
use App\Services\VisitDestinationService;
use App\Support\PurposeNormalizer;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class CheckInController extends Controller
{
    public $disable;

    function __construct()
    {
    }

    public function index()
    {
        session()->forget('visitor');
        session()->forget('is_returned');
        return view('frontend.check-in.home-page');
    }
    public function scanQr()
    {
        return view('frontend.check-in.cameraPreview');
    }


    public function createStepOne(Request $request)
    {
        $employees = $this->employeesForVisitorForm();
        $visitor = (object)$request->session()->get('visitor');

        $employee_id = "";
        $purpose = "";
        $company_name = "";
        $disable =  false;
        if (!blank($visitor) && isset($visitor->id)) {
            $role = auth()->user() ? auth()->user()->myrole : 0;
            if (session()->has('pre-register')) {
                $visitingDetails = PreRegister::where('visitor_id', $visitor->id)->latest()->first();
                if (!blank($visitingDetails)) {
                    $employee_id = $visitingDetails->employee_id;
                }
            } else {
                $visitingDetails = VisitingDetails::where('visitor_id', $visitor->id)->latest()->first();
                if (!blank($visitingDetails)) {
                    $company_name = $visitingDetails->company_name;
                    $employee_id = $visitingDetails->employee_id;
                    $purpose = $visitingDetails->purpose;
                }
            }
        }
        if (!blank($visitor) && isset($visitor->disable)) {
            $disable =  $visitor->disable;
        }

        return view('frontend.check-in.step-one', compact('employees', 'visitor', 'company_name', 'disable', 'employee_id', 'purpose'));
    }


    public function postCreateStepOne(Request $request)
    {
        if ($request->filled('employee_id') && !$this->employeeAllowedForCurrentUser((int) $request->input('employee_id'))) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['employee_id' => 'El funcionario seleccionado no pertenece a su sede.']);
        }

        if ($request->session()->get('is_returned') == false || empty($request->session()->get('is_returned'))) {
            $emailValidation = '';

            if (!blank($request->get('email'))) {
                $emailValidation = $request->validate([
                    'email'                      => 'unique:visitors,email',
                ]);
            }

            $termsConditionsAcceptStatusValidation = '';
            if (setting('terms_visibility_status')) {
                $termsConditionsAcceptStatusValidation =   $request->validate([
                    'accept_tc'                      => 'accepted',
                ]);
            }

            $validatedData = $request->validate([
                'first_name'                 => 'required',
                'last_name'                  => 'required',
                'phone'                      => 'required|unique:visitors,phone',
                'purpose'                    => 'required',
                'employee_id'                => 'required|numeric',
                'gender'                     => 'required|numeric',
                'company_name'               => '',
                'company_employee_id'        => '',
                'national_identification_no' => 'required|unique:visitors,national_identification_no',
                'is_group_enabled'           => '',
                'address'                    => '',
                'oldVisitor'                 => '',
            ]);


            if (!blank($emailValidation)) {
                $validatedData = array_merge($validatedData, $emailValidation);
            }
            if (setting('terms_visibility_status')) {
                $validatedData = array_merge($validatedData, $termsConditionsAcceptStatusValidation);
            }
        } else {
            // Buscar visitante existente solo por campos no vacíos
            $visitor = Visitor::where(function($query) use ($request) {
                if (!blank($request->get('email'))) {
                    $query->orWhere('email', $request->get('email'));
                }
                if (!blank($request->get('phone'))) {
                    $query->orWhere('phone', $request->get('phone'));
                }
                if (!blank($request->get('national_identification_no'))) {
                    $query->orWhere('national_identification_no', $request->get('national_identification_no'));
                }
            })->first();
            
            $national_identification_no = "";
            if ($visitor) {
                // Visitante encontrado - excluirlo de validación de unicidad
                $email = blank($request->get('email')) ? '' : ['email', 'string', 'unique:visitors,email,' . $visitor->id];
                $phone = ['required', 'string', Rule::unique("visitors", "phone")->ignore($visitor)];
                $national_identification_no = ['required',  'string', 'unique:visitors,national_identification_no,'.$visitor->id];

            } else {
                // Visitante NO encontrado - validar unicidad completa
                $email                      = blank($request->get('email')) ? '' : ['email', 'string', 'unique:visitors,email'];
                $phone                      = ['required', 'string', 'unique:visitors,phone'];
                $national_identification_no = ['required',  'string', 'unique:visitors,national_identification_no'];
            }

            if (setting('terms_visibility_status')) {
                $termsConditionsAcceptStatusValidation =   $request->validate([
                    'accept_tc'                      => 'accepted',
                ]);
            }

            $validatedData = $request->validate([
                'first_name'                 => 'required',
                'last_name'                  => 'required',
                'email'                      => $email,
                'phone'                      => $phone,
                'purpose'                    => 'required',
                'employee_id'                => 'required|numeric',
                'gender'                     => 'required|numeric',
                'company_name'               => '',
                'company_employee_id'        => '',
                'national_identification_no' => $national_identification_no,
                'is_group_enabled'           => '',
                'address'                    => '',
                'oldVisitor'                 => '',

            ]);


            if (setting('terms_visibility_status')) {
                $validatedData = array_merge($validatedData, $termsConditionsAcceptStatusValidation);
            }
        }
        $request->session()->put('visitor', $validatedData);
        
        // Si es visitante recurrente, saltar step-two y crear registro directamente
        if ($request->session()->get('is_returned') == true) {
            return $this->store($request);
        }
        
        return redirect()->route('check-in.step-two');
    }


    public function createStepTwo(Request $request)
    {
        $visitingDetails = $request->session()->get('visitor');
        $employee = Employee::find($visitingDetails['employee_id']);


        $visitor = Visitor::where('phone', $visitingDetails['phone'])->first();
        if ($visitor) {
            $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
            if ($visitorDetail) {
                $image = $visitorDetail->images;
            } elseif (!setting('photo_capture_enable')) {
                $image = '';
            } else {
                $image = 'default/user.png';
            }
            return view('frontend.check-in.step-two', compact('employee', 'visitingDetails', 'image'));
        } else {
            if (setting('photo_capture_enable')) {
                $image = 'default/user.png';
            } else {
                $image = '';
            }
            return view('frontend.check-in.step-two', compact('employee', 'visitingDetails', 'image'));
        }
    }


    public function store(Request $request)
    {
        $getVisitor = $request->session()->get('visitor');
        if ($getVisitor) {
            $imageName = null;
            if ($request->has('photo')) {
                if (setting('photo_capture_enable')) {
                    $request->validate([
                        'photo' => 'required',
                    ]);
                }

                $encoded_data = $request['photo'];
                $image = str_replace('data:image/png;base64,', '', $encoded_data);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . 'png';
                $tempPath = storage_path('app/temp/' . $imageName);
                file_put_contents($tempPath, base64_decode($image));
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize($tempPath);
            }
        } else {
            redirect()->route('check-in.step-one')->with('error', 'visitor information not found, fill again!');
        }

        $visitorID = DB::table('visiting_details')->max('id');
        $visitorReg = VisitingDetails::find($visitorID);
        $date = date('y-m-d');
        $data = substr($date, 0, 2);
        $data1 = substr($date, 3, 2);
        $data2 = substr($date, 6, 8);
        $today = $data2 . $data1 . $data;

        if (!blank($visitorReg)) {
            $lastentrydmy = substr($visitorReg->reg_no, 0, 6);
            if ($lastentrydmy == $today) {
                $value = substr($visitorReg->reg_no, 6);
                $value += 1;
                $reg_no = $data2 . $data1 . $data . $value;
            } else {
                $reg_no = $data2 . $data1 . $data . '1';
            }
        } else {
            $reg_no = $data2 . $data1 . $data . '1';
        }
        if ($request->session()->get('is_returned') == false || empty($request->session()->get('is_returned'))) {
            // Obtener el usuario autenticado para asignar creador/editor
            $currentUser = Auth::user();
            $userId = $currentUser ? $currentUser->id : 1;
            
            $input['first_name']                 = $getVisitor['first_name'];
            $input['last_name']                  = $getVisitor['last_name'];
            $input['email']                      = isset($getVisitor['email']) ? $getVisitor['email'] : "";
            $input['phone']                      = preg_replace("/[^0-9]/", "", $getVisitor['phone']);
            $input['gender']                     = $getVisitor['gender'];
            $input['address']                    = $getVisitor['address'];
            $input['national_identification_no'] = $getVisitor['national_identification_no'] ? $getVisitor['national_identification_no'] : "";
            $input['is_pre_register']            = false;
            $input['status']                     = Status::ACTIVE;
            $input['creator_id']                 = $userId;
            $input['creator_type']               = 'App\Models\User';
            $input['editor_type']                = 'App\Models\User';
            $input['editor_id']                  = $userId;
            //Qrcode Genarate
            $file_name = 'qrcode-' . preg_replace("/[^0-9]/", "", $getVisitor['phone']) . '.png';
            $input['barcode']   = $file_name;
            $file = public_path('qrcode/' . $file_name);
            QRCode::size(300)->format('png')->generate(route('checkin.visitor-details', preg_replace("/[^0-9]/", "", $getVisitor['phone'])), $file);
            $visitor = Visitor::create($input);
        } else {
            $visitor                             = Visitor::where('phone', $getVisitor['phone'])->first();
            $visitor->first_name                 = $getVisitor['first_name'];
            $visitor->last_name                  = $getVisitor['last_name'];
            $visitor->email                      = $getVisitor['email'];
            $visitor->national_identification_no = $getVisitor['national_identification_no'];
            $visitor->gender                     = $getVisitor['gender'];
            $visitor->address                    = $getVisitor['address'];
            $visitor->is_pre_register            = false;

            $file_name = 'qrcode-' . preg_replace("/[^0-9]/", "", $getVisitor['phone']) . '.png';
            $visitor->barcode = $file_name;
            $file = public_path('qrcode/' . $file_name);
            QRCode::size(300)->format('png')->generate(route('checkin.visitor-details', preg_replace("/[^0-9]/", "", $getVisitor['phone'])), $file);
            $visitor->save();
        }


        if ($visitor) {
            // Obtener la información del empleado para asignar región y sede
            $employee = Employee::find($getVisitor['employee_id']);

            // Obtener el usuario autenticado para asignar creador/editor
            $currentUser = Auth::user();
            $userId = $currentUser ? $currentUser->id : 1;

            $visiting['reg_no']       = $reg_no;
            $visiting['purpose']      = PurposeNormalizer::canonicalize($getVisitor['purpose']);
            $visiting['company_name'] = $getVisitor['company_name'];
            $visiting['employee_id']  = $getVisitor['employee_id'];
            $visiting['visitor_id']   = $visitor->id;
            $visiting['region_id']    = $employee ? $employee->region_id : null;
            $visiting['headquarters_id'] = $employee ? $employee->headquarters_id : null;
            $visiting['status']       = VisitorStatus::PENDDING;
            // user_id debe apuntar a la tabla users, no al id del empleado
            $visiting['user_id']      = $employee && $employee->user_id ? $employee->user_id : $userId;
            $visiting['creator_id']   = $userId;
            $visiting['creator_type'] = 'App\Models\User';
            $visiting['editor_type']  = 'App\Models\User';
            $visiting['editor_id']    = $userId;
            app(VisitDestinationService::class)->applyToVisitingPayload($visiting, $employee);
            $visitingDetails          = VisitingDetails::create($visiting);
            
            if ($imageName) {
                // Nueva foto capturada
                $visitingDetails->addMedia($tempPath)->toMediaCollection('visitor');
                File::delete($tempPath);
            } elseif ($request->session()->get('is_returned') == true) {
                // Visitante recurrente sin nueva foto - copiar foto anterior
                $previousVisit = VisitingDetails::where('visitor_id', $visitor->id)
                    ->whereNotNull('id')
                    ->where('id', '!=', $visitingDetails->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($previousVisit && $previousVisit->getFirstMedia('visitor')) {
                    $previousMedia = $previousVisit->getFirstMedia('visitor');
                    
                    try {
                        // Obtener la ruta del archivo
                        $mediaPath = $previousMedia->getPath();
                        
                        // Verificar que el archivo existe físicamente antes de intentar copiarlo
                        if (File::exists($mediaPath)) {
                            $visitingDetails->addMedia($mediaPath)
                                ->preservingOriginal()
                                ->toMediaCollection('visitor');
                        } else {
                            // Si el archivo no existe físicamente, registrar el problema
                            // pero no lanzar excepción. El sistema usará la imagen por defecto
                            // definida en VisitingDetails::getImagesAttribute()
                            \Log::warning('Archivo de media no encontrado al copiar de visita anterior', [
                                'visitor_id' => $visitor->id,
                                'previous_visit_id' => $previousVisit->id,
                                'media_id' => $previousMedia->id,
                                'expected_path' => $mediaPath
                            ]);
                        }
                    } catch (FileDoesNotExist $e) {
                        // Capturar específicamente el error de archivo no encontrado
                        \Log::warning('Error de Spatie MediaLibrary al copiar media: ' . $e->getMessage(), [
                            'visitor_id' => $visitor->id,
                            'previous_visit_id' => $previousVisit->id,
                            'media_id' => $previousMedia->id ?? null
                        ]);
                        // Continuar sin copiar el media - el sistema usará la imagen por defecto
                    } catch (\Exception $e) {
                        // Capturar cualquier otro error al copiar el media
                        \Log::error('Error inesperado al copiar media de visita anterior: ' . $e->getMessage(), [
                            'visitor_id' => $visitor->id,
                            'previous_visit_id' => $previousVisit->id,
                            'media_id' => $previousMedia->id ?? null,
                            'exception' => get_class($e)
                        ]);
                        // Continuar sin copiar el media - el sistema usará la imagen por defecto
                    }
                }
            }

            try {

                $token = app(JwtTokenService::class)->jwtToken($visitingDetails);
                $visitingDetails->employee->user->notify(new EmployeConfirmation($visitingDetails, $token));
            } catch (\Exception $e) {
            }

            // DESHABILITADO: Notificaciones push causan timeout de 300s al no poder conectar con fcm.googleapis.com
            // try {
            //     app(PushNotificationService::class)->sendWebNotification($visitingDetails);
            // } catch (\Exception $exception) {
            // }

            // try {
            //     app(PushNotificationService::class)->sendPushNotification($visitingDetails, $visitingDetails->employee->email);
            // } catch (\Exception $exception) {
            // }

            try {
                app(VisitDestinationService::class)->notifyDestinationReceivers($visitingDetails);
            } catch (\Exception $exception) {
            }
        }


        return redirect()->route('check-in.show', $visitingDetails->id);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $visitingDetails = VisitingDetails::find($id);
        // Usar la imagen del VisitingDetails actual, no del primero
        $visitingDetails['photo'] = $visitingDetails->images;

        if ($visitingDetails) {
            return view('frontend.check-in.show', compact('visitingDetails'));
        } else {
            session()->forget('visitor');
            session()->forget('is_returned');
            return redirect('/check-in');
        }
    }

    public function visitor_return()
    {
        return view('frontend.check-in.return');
    }

    public function find_visitor(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required',
                ],
            ],
            [
                'email.required' => 'The email/phone/national ID field is required. ',
            ]
        );
        $validator->after(function ($validator) use ($request) {
            if (!$this->checkVisitor(false, $request)) {
                $validator->errors()->add('email', 'Visitor not found!');
            }
        });


        if ($validator->fails()) {
            return redirect()->route('check-in.return')
                ->withErrors($validator)
                ->withInput();
        }

        $visitor = Visitor::where(function ($query) use ($request) {
            $query->orWhere(function ($emailQuery) use ($request) {
                $emailQuery->where(['email' => $request->email, 'is_pre_register' => false]);
            });
            $query->orWhere(function ($phoneQuery) use ($request) {
                $phoneQuery->where(['phone' => $request->email, 'is_pre_register' => false]);
            });
            $query->orWhere(function ($nationalIdentificationQuery) use ($request) {
                $nationalIdentificationQuery->where(['national_identification_no' => $request->email, 'is_pre_register' => false]);
            });
        })->first();


        $visitor_Detail = VisitingDetails::select('visitor_id')->where('visitor_id', $visitor->id)->where('disable', true)->orderBy('created_at', 'desc')->first();

        //Check User block or not
        if ($visitor_Detail) {
            $blockedVisitor = Visitor::find($visitor->id);
            $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
            if ($visitorDetail) {
                $blockedVisitor->image = $visitorDetail->images;
            }
            return redirect()->route('home')->with('blocked_visitor', $blockedVisitor);
        }

        if (!empty($visitor)) {
            $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
            if ($visitorDetail) {
                $visitor->image = $visitorDetail->images;
            }
            $request->session()->put('visitor', $visitor);
            if (@Auth::user()->id == 1) {
                $visitor->disable = false;
            } else {
                $visitor->disable = true;
            }
            $request->session()->put('is_returned', true);
            return redirect()->route('check-in.step-one');
        }
        return redirect()->route('check-in.return');
    }

    public function checkVisitor($boolean, $request)
    {


        $visitor = Visitor::where(function ($query) use ($request, $boolean) {
            $query->orWhere(function ($emailQuery) use ($request, $boolean) {
                $emailQuery->where(['email' => $request->email, 'is_pre_register' => $boolean]);
            });
            $query->orWhere(function ($phoneQuery) use ($request, $boolean) {
                $phoneQuery->where(['phone' => $request->email, 'is_pre_register' => $boolean]);
            });
            $query->orWhere(function ($nationalIdentificationQuery) use ($request, $boolean) {
                $nationalIdentificationQuery->where(['national_identification_no' => $request->email, 'is_pre_register' => $boolean]);
            });
        })->first();

        if ($visitor) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPreRegister($boolean, $request)
    {


        $visitor = Visitor::where(function ($query) use ($request, $boolean) {
            $query->orWhere(function ($emailQuery) use ($request, $boolean) {
                $emailQuery->where(['email' => $request->email, 'is_pre_register' => $boolean]);
            });
            $query->orWhere(function ($phoneQuery) use ($request, $boolean) {
                $phoneQuery->where(['phone' => $request->email, 'is_pre_register' => $boolean]);
            });
            $query->orWhere(function ($nationalIdentificationQuery) use ($request, $boolean) {
                $nationalIdentificationQuery->where(['national_identification_no' => $request->email, 'is_pre_register' => $boolean]);
            });
        })->first();

        if ($visitor) {
            return true;
        } else {
            return false;
        }
    }

    public function find_pre_visitor(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => [
                    'required',
                ],
            ],
            [
                'email.required' => 'The email/phone/national ID field is required. ',
            ]
        );
        $validator->after(function ($validator) use ($request) {
            if (!$this->checkPreRegister(true, $request)) {
                $validator->errors()->add('email', 'Pre-Register not found!');
            }
        });



        if ($validator->fails()) {
            return redirect()->route('check-in.pre.registered')
                ->withErrors($validator)
                ->withInput();
        }
        $visitor = Visitor::where(function ($query) use ($request) {
            $query->orWhere(function ($emailQuery) use ($request) {
                $emailQuery->where(['email' => $request->email, 'is_pre_register' => true]);
            });
            $query->orWhere(function ($phoneQuery) use ($request) {
                $phoneQuery->where(['phone' => $request->email, 'is_pre_register' => true]);
            });
            $query->orWhere(function ($nationalIdentificationQuery) use ($request) {
                $nationalIdentificationQuery->where(['national_identification_no' => $request->email, 'is_pre_register' => true]);
            });
        })->first();

        $today = Carbon::now()->toDateString();
        if ($visitor) {
            $visitDetails = PreRegister::select('expected_date')->where('visitor_id', $visitor->id)->where('expected_date', '<=', $today)->first();

            if (!$visitDetails) {
                return redirect()->back()->with('error', 'Sorry, Your Appoinment date has not arrived yet !');
            }
        }
        if (!empty($visitor)) {
            $preData = PreRegister::where('visitor_id', $visitor->id)->first();
            $visitor->employee_id = $preData->employee_id;

            if (@Auth::user()->id == 1) {
                $visitor->disable = false;
            } else {
                $visitor->disable = true;
            }
            $request->session()->put('visitor', $visitor);
            $request->session()->put('pre-register', true);
            $request->session()->put('is_returned', true);

            return redirect()->route('check-in.step-one');
        }

        return redirect()->route('check-in.pre.registered');
    }

    public function pre_registered()
    {
        return view('frontend.check-in.pre_registered');
    }

    public function visitorDetails($visitorPhone)
    {
        $visitor = Visitor::where('phone', $visitorPhone)->first();

        if ($visitor === null) {
            $employee = Employee::where('phone', $visitorPhone)->first();

            if ($employee) {
                $checkout = Attendance::where(['user_id' => $employee->id, 'date' => date('Y-m-d')])->first();

                if ($checkout === null) {
                    $checkout               = new Attendance;
                    $checkout->title        = 'Office';
                    $checkout->checkin_time = date('g:i A');
                    $checkout->date         = date('Y-m-d');
                    $checkout->user_id      = $employee->id;
                    $checkout->save();

                    return redirect()->route('home')->withSuccess("Check-in Successful!");
                } else {
                    $checkout->checkout_time     = date('g:i A');
                    $checkout->save();

                    return redirect()->route('home')->withSuccess("Check-out Successful!");
                }
            } else {
                return redirect()->route('home')->withWarning('No record found!');
            }
        } else {
            $visitor_Detail = VisitingDetails::select('visitor_id')->where('visitor_id', $visitor->id)->where('disable', true)->orderBy('created_at', 'desc')->first();

            //Check User block or not
            if ($visitor_Detail) {
                $blockedVisitor = Visitor::find($visitor->id);
                $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
                if ($visitorDetail) {
                    $blockedVisitor->image = $visitorDetail->images;
                }
                return redirect()->route('home')->with('blocked_visitor', $blockedVisitor);
            } else {
                $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
                if (!empty($visitor)) {

                    if ($visitorDetail) {

                        $visitor->image = $visitorDetail->images;
                    }
                    session()->put('visitor', $visitor);
                    if (@Auth::user()->id == 1) {
                        $visitor->disable = false;
                    } else {
                        $visitor->disable = true;
                    }
                    session()->put('is_returned', true);
                    return redirect()->route('check-in.step-one');
                }
            }
        }
    }

    public function preVisitorDetails($visitorPhone)
    {

        $visitor = Visitor::where('phone', $visitorPhone)->first();

        $visitor_Detail = VisitingDetails::select('visitor_id')->where('visitor_id', $visitor->id)->where('disable', true)->orderBy('created_at', 'desc')->first();

        //Check User block or not
        if ($visitor_Detail) {
            $blockedVisitor = Visitor::find($visitor->id);
            $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
            if ($visitorDetail) {
                $blockedVisitor->image = $visitorDetail->images;
            }
            return redirect()->route('home')->with('blocked_visitor', $blockedVisitor);
        } else {

            $today = Carbon::now()->toDateString();
            if ($visitor) {
                $visitDetails = PreRegister::select('expected_date')->where('visitor_id', $visitor->id)->where('expected_date', '<=', $today)->first();

                if (!$visitDetails) {
                    return redirect()->back()->with('error', 'Sorry, Your Appoinment date has not arrived yet !');
                }
            }
            if (!empty($visitor)) {
                $visitorDetail = VisitingDetails::where('visitor_id', $visitor->id)->first();
                if ($visitorDetail) {
                    $visitor->image = $visitorDetail->images;
                }
                $preData = PreRegister::where('visitor_id', $visitor->id)->first();
                $visitor->employee_id = $preData->employee_id;

                if (@Auth::user()->id == 1) {
                    $visitor->disable = false;
                } else {
                    $visitor->disable = true;
                }

                session()->put('visitor', $visitor);
                session()->put('is_returned', true);
                return redirect()->route('check-in.step-one');
            }
        }
    }

    private function getUserHeadquartersId(): ?int
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('supervisor') && !$user->hasRole('Reception'))) {
            return null;
        }

        $headquartersId = optional($user->employee)->headquarters_id;

        return $headquartersId ? (int) $headquartersId : null;
    }

    private function employeesForVisitorForm()
    {
        $query = Employee::query()->where('status', Status::ACTIVE);

        $headquartersId = $this->getUserHeadquartersId();
        if ($headquartersId) {
            $query->where('headquarters_id', $headquartersId);
        }

        return $query->orderBy('first_name')->orderBy('last_name')->get();
    }

    private function employeeAllowedForCurrentUser(?int $employeeId): bool
    {
        if (!$employeeId) {
            return false;
        }

        $headquartersId = $this->getUserHeadquartersId();
        if (!$headquartersId) {
            return Employee::where('id', $employeeId)->where('status', Status::ACTIVE)->exists();
        }

        return Employee::where('id', $employeeId)
            ->where('status', Status::ACTIVE)
            ->where('headquarters_id', $headquartersId)
            ->exists();
    }
}
