/**
 * Created by shennemann on 22.02.2017.
 *
 * not in gulp, cause it is only needed for internal cases
 */


var inlineEdit = function() {
    var win = window,
        doc = win.document,
        self = this,

        // private methods
        init,
        initEvents,
        initTinyMCe,
        initClasses;

    /**
     * Construct
     */
    init = function() {
        initTinyMCe();
        initClasses();
        initEvents();
    };

    initClasses = function() {
        window.columnEdit = new inlineEditColumns();
    };

    /**
     * Close edit-box
     */
    self.closeInlineEdit = function() {
        $('.edit-open').removeClass('edit-open');
        $('.inline-edit-box').data({});
        $('.inline-edit-box').removeData();
        $('.inline-edit-box').removeClass('active');
        $('.inline-edit-box textarea').val('');
        $('.inline-edit-box-title').html('');
    };


    /**
     * Init tinymce
     */
    initTinyMCe = function() {

    };

    /**
     * init events
     */
    initEvents = function() {
        /**
         * Onclick cancel button
         */
        jQuery('.inline-edit-cancel-button, .inline-edit-box .close-button').on('click', self.closeInlineEdit);


        /**
         * Save content
         */
        jQuery('.inline-edit-save-button').on('click', function(e) {
            //CKEDITOR.instances['inline-edit-field'].updateElement();
            var data = $('.inline-edit-box').data();
            data.newContent = $('.inline-edit-box textarea').val();

            if (data.columnid) {
                // save column
                columnEdit.saveColumn(data);
            }
        });


        /**
         * Prevent link clicking
         */
        jQuery(document).on('click', '.edit-mode .div-area a', function(e) {
            e.preventDefault();
        });

        /**
         * Hover/mouseenter for editable elements
         *  Must done with js, cause propagation
         */
        jQuery(document).on('mouseover',
            '.edit-mode .div-area, ' +
            '.edit-mode .mc-column, ' +
            '.edit-mode .inline-edit-modul, ' +
            '.edit-mode .inline-edit-admin',
            function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('.inline-edit-hover').removeClass('inline-edit-hover');
                $(e.currentTarget).addClass('inline-edit-hover');
            });

        /**
         * Hover/mouseleave for editable elements
         *  Must done with js, cause propagation
         */
        jQuery(document).on('mouseleave',
            '.edit-mode .div-area, ' +
            '.edit-mode .mc-column, ' +
            '.edit-mode .inline-edit-modul, ' +
            '.edit-mode .inline-edit-admin',
            function(e) {
                $('.inline-edit-hover').removeClass('inline-edit-hover');
            });

        /**
         * Resize cke on resize
         */
        /*jQuery(window).on('resize', function() {
            if (typeof(CKEDITOR.instances['inline-edit-field']) !== 'undefined') {
                CKEDITOR.instances['inline-edit-field'].resize('100%', $('.inline-edit-box').height() - 170, true);
            }
        });*/
    };

    init();
};

window.inlineEditInstance = new inlineEdit();