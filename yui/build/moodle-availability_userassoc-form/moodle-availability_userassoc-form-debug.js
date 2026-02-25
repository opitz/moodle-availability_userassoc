YUI.add('moodle-availability_userassoc-form', function (Y, NAME) {

 /**
     * JavaScript for form editing user association conditions.
     *
     * @module moodle-availability_userassoc-form
     */
    M.availability_userassoc = M.availability_userassoc || {};

    /**
     * @class M.availability_userassoc.form
     * @extends M.core_availability.plugin
     */
    M.availability_userassoc.form = Y.Object(M.core_availability.plugin);

    /**
     * Initialise the plugin (called by core availability).
     *
     * @method initInner
     */
    M.availability_userassoc.form.initInner = function () {
        // Nothing required.
    };

    /**
     * Gets the human-readable node for the UI.
     *
     * Stored JSON: {type:'userassoc', letters:'S,U,P,V,H'}
     */
    M.availability_userassoc.form.getNode = function (json) {
        json = json || {};

        var letters = (json.letters !== undefined) ? json.letters : '';

        var html =
            '<span class="availability_userassoc">' +
            '<label><span class="pe-3">' +
            M.util.get_string('title', 'availability_userassoc') +
            '</span>' +
            '<input name="letters" type="text" class="form-control" style="width: 14em; display:inline-block;" ' +
            'placeholder="S,U,P,V,H" ' +
            'title="' + M.util.get_string('label', 'availability_userassoc') + '"/>' +
            '</label>' +
            '<span style="margin-left:8px;opacity:.8;">(empty employee_details = alumni)</span>' +
            '</span>';

        var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');

        // Set initial value.
        node.one('input[name=letters]').set('value', letters);

        // Add change handler (first time only).
        if (!M.availability_userassoc.form.addedEvents) {
            M.availability_userassoc.form.addedEvents = true;
            var root = Y.one('.availability-field');
            if (root) {
                root.delegate('change', function () {
                    M.core_availability.form.update();
                }, '.availability_userassoc input[name=letters]');
            }
        }

        return node;
    };

    M.availability_userassoc.form.fillValue = function (value, node) {
        value.letters = node.one('input[name=letters]').get('value');
    };

    M.availability_userassoc.form.fillErrors = function (errors, node) {
        var value = {};
        this.fillValue(value, node);

        var v = (value.letters || '').toUpperCase().replace(/\s+/g, '');

        // Allow either:
        // - "S,U,P" style CSV of letters
        // - or a single letter like "S"
        // This regex allows A-Z only, comma-separated.
        var ok = /^[A-Z](,[A-Z])*$/.test(v);

        if (!ok) {
            errors.push('availability_userassoc:error_letters');
        }
    };


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
