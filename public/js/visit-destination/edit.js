'use strict';

$(document).ready(function () {
    $('#user_ids').select2({
        placeholder: 'Seleccione usuarios de recepcion',
        width: '100%',
    });

    function syncRuleValueFields() {
        const selectedType = $('#rule_type').val();

        $('.rule-value-group').addClass('d-none');
        $('.rule-value-select').prop('disabled', true).prop('name', '');

        const activeGroup = $('.rule-value-group[data-type="' + selectedType + '"]');
        const activeSelect = activeGroup.find('.rule-value-select');

        activeGroup.removeClass('d-none');
        activeSelect.prop('disabled', false).prop('name', 'rule_value');
    }

    $('#rule_type').on('change', syncRuleValueFields);
    syncRuleValueFields();
});
