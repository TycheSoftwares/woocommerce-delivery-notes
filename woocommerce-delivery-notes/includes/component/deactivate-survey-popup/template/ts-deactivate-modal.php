<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slug                      = $VARS[ 'slug' ];
$confirmation_message      = '';
$reasons                   = $VARS['reasons']['default'];
$reasons_list_items_html   = '';
$plugin_customized_reasons = array();
$incr                      = 0;

foreach ( $reasons as $reason ) {
	$list_item_classes           = 'reason' . ( ! empty( $reason['input_type'] ) ? ' has-input' : '' ) . ( ( isset( $reason[ 'html' ] ) && ( ! empty( $reason[ 'html' ] ) ) ) ? ' has_html' : '' );
	
    $reason_html                 = ( isset( $reason['html'] ) && ( ! empty( $reason['html'] ) ) ) ? '<div class="reason_html">' . $reason['html'] . '</div>' : '';

    $ts_reason_input_type        = ( isset( $reason['input_type'] ) && ( ! empty( $reason['input_type'] ) ) ) ?  $reason['input_type']  : '';

    $ts_reason_input_placeholder = ( isset( $reason['input_placeholder'] ) && ( ! empty( $reason['input_placeholder'] ) ) ) ?  $reason['input_placeholder']  : '';

    $ts_reason_id                = ( isset( $reason['id'] ) && ( ! empty( $reason['id'] ) ) ) ?  $reason['id'] : '';

    $ts_reason_text              = ( isset( $reason['text'] ) && ( ! empty( $reason['text'] ) ) ) ?  $reason['text']  : '';
    
    $selected = "";
    if ( $incr == 0 ) { 
        $selected = "checked";
    }

	$reasons_list_items_html .= '<li class="' . $list_item_classes . '" data-input-type="' . $ts_reason_input_type . '" data-input-placeholder="' . $ts_reason_input_placeholder . '"><label><span><input type="radio" name="selected-reason" value="' . $ts_reason_id . '" ' . $selected . '/></span><span>' . $ts_reason_text . '</span></label>' . $reason_html . '</li>';
	$incr ++;
}
 
?>
<style>
    .ts-modal {
        position: fixed;
        overflow: auto;
        height: 100%;
        width: 100%;
        top: 0;
        z-index: 100000;
        display: none;
        background: rgba(0, 0, 0, 0.6)
    }

    .ts-modal .ts-modal-dialog {
        background: transparent;
        position: absolute;
        left: 50%;
        margin-left: -298px;
        padding-bottom: 30px;
        top: -100%;
        z-index: 100001;
        width: 596px
    }

    .ts-modal li.reason.has_html .reason_html {
        display: none;
        border: 1px solid #ddd;
        padding: 4px 6px;
        margin: 6px 0 0 20px;
    }

    .ts-modal li.reason.has_html.li-active .reason_html {
        display: block;
    }

    @media (max-width: 650px) {
        .ts-modal .ts-modal-dialog {
            margin-left: -50%;
            box-sizing: border-box;
            padding-left: 10px;
            padding-right: 10px;
            width: 100%
        }

        .ts-modal .ts-modal-dialog .ts-modal-panel > h3 > strong {
            font-size: 1.3em
        }

        .ts-modal .ts-modal-dialog li.reason {
            margin-bottom: 10px
        }

        .ts-modal .ts-modal-dialog li.reason .reason-input {
            margin-left: 29px
        }

        .ts-modal .ts-modal-dialog li.reason label {
            display: table
        }

        .ts-modal .ts-modal-dialog li.reason label > span {
            display: table-cell;
            font-size: 1.3em
        }
    }

    .ts-modal.active {
        display: block
    }

    .ts-modal.active:before {
        display: block
    }

    .ts-modal.active .ts-modal-dialog {
        top: 10%
    }

    .ts-modal .ts-modal-body, .ts-modal .ts-modal-footer {
        border: 0;
        background: #fefefe;
        padding: 20px
    }

    .ts-modal .ts-modal-body {
        border-bottom: 0
    }

    .ts-modal .ts-modal-body h2 {
        font-size: 20px
    }

    .ts-modal .ts-modal-body > div {
        margin-top: 10px
    }

    .ts-modal .ts-modal-body > div h2 {
        font-weight: bold;
        font-size: 20px;
        margin-top: 0
    }

    .ts-modal .ts-modal-footer {
        border-top: #eeeeee solid 1px;
        text-align: right
    }

    .ts-modal .ts-modal-footer > .button {
        margin: 0 7px
    }

    .ts-modal .ts-modal-footer > .button:first-child {
        margin: 0
    }

    .ts-modal .ts-modal-panel:not(.active) {
        display: none
    }

    .ts-modal .reason-input {
        margin: 3px 0 3px 22px
    }

    .ts-modal .reason-input input, .ts-modal .reason-input textarea {
        width: 100%
    }

    body.has-ts-modal {
        overflow: hidden
    }

    #the-list .deactivate > .wcdn-ts-slug {
        display: none
    }

    .ts-modal li.reason-hide {
        display: none;
    }

</style>
<script type="text/javascript">
    var currentPluginName = "";
    var TSCustomReasons = {};
    var TSDefaultReason = {};
    ( function ($) {
        var $deactivateLinks = {};
        var reasonsHtml = <?php echo json_encode( $reasons_list_items_html ); ?>,
            modalHtml =
                '<div class="ts-modal<?php echo ( $confirmation_message == "" ) ? ' no-confirmation-message' : ''; ?>">'
                + ' <div class="ts-modal-dialog">'
                + '     <div class="ts-modal-body">'
                + '         <div class="ts-modal-panel" data-panel-id="confirm"><p><?php echo $confirmation_message; ?></p></div>'
                + '         <div class="ts-modal-panel active" data-panel-id="reasons"><h3><strong><?php printf( WCDN_TS_deactivate::load_str( 'deactivation-share-reason' ) ); ?>:</strong></h3><ul id="reasons-list">' + reasonsHtml + '</ul></div>'
                + '     </div>'
                + '     <div class="ts-modal-footer">'
                + '         <a href="#" class="button button-secondary button-deactivate"></a>'
                + '         <a href="#" class="button button-primary button-close"><?php printf( WCDN_TS_deactivate::load_str( 'deactivation-modal-button-cancel' ) ); ?></a>'
                + '     </div>'
                + ' </div>'
                + '</div>',
            $modal = $(modalHtml),

            $deactivateLink = $('#the-list .deactivate > .wcdn-ts-slug').prev();

        for( var i = 0; i < $deactivateLink.length; i++ ) {
            $deactivateLinks[ $( $deactivateLink[i] ).siblings( ".wcdn-ts-slug" ).attr( 'data-slug' ) ] = $deactivateLink[i].href;
        }
   
        $modal.appendTo( $( 'body' ) );

        registerEventHandlers();

        function registerEventHandlers() {
            $deactivateLink.on( "click", function (evt) {
                evt.preventDefault();
                currentPluginName = $(this).siblings( ".wcdn-ts-slug" ).attr( 'data-slug' );
                showModal();
            });

            $modal.on( 'click', '.button', function (evt) {
                evt.preventDefault();
                if ($(this).hasClass( 'disabled' ) ) {
                    return;
                }

                var _parent = $(this).parents( '.ts-modal:first' );
                var _this = $(this);

                if( _this.hasClass( 'allow-deactivate' ) ) {
                    var $radio = $('input[type="radio"]:checked');
                    var $selected_reason = $radio.parents('li:first'),
                        $input = $selected_reason.find('textarea, input[type="text"]');
                    if( $radio.length == 0 ) {
                        var data = {
                            'action': 'ts_submit_uninstall_reason',
                            'reason_id': 0,
                            'reason_text': "Deactivated without any option",
                            'plugin_basename': currentPluginName,
                        };
                    } else {
                        var data = {
                            'action': 'ts_submit_uninstall_reason',
                            'reason_id': (0 !== $radio.length) ? $radio.val() : '',
                            'reason_text': $selected_reason.text(),
                            'reason_info': (0 !== $input.length) ? $input.val().trim() : '',
                            'plugin_basename': currentPluginName,
                        };
                    }
                    
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: data,
                        beforeSend: function () {
                            _parent.find('.button').addClass('disabled');
                            _parent.find('.button-secondary').text('Processing...');
                        },
                        complete: function () {
                            // Do not show the dialog box, deactivate the plugin.
                            window.location.href = $deactivateLinks[currentPluginName];
                        }
                    });
                }
            });

            $modal.on('click', 'input[type="radio"]', function () {
                console.log( this );
                var _parent = $(this).parents('li:first');
                var _parent_ul = $(this).parents('ul#reasons-list');

                _parent_ul.children("li.li-active").removeClass("li-active");

                $modal.find('.reason-input').remove();
                $modal.find('.button-deactivate').text('<?php printf( WCDN_TS_deactivate::load_str( 'deactivation-modal-button-submit' ) ); ?>');

                if (_parent.hasClass('has_html')) {
                    _parent.addClass('li-active');
                }
                if (_parent.hasClass('has-input')) {
                    var inputType = _parent.data('input-type'),
                        inputPlaceholder = _parent.data('input-placeholder'),
                        reasonInputHtml = '<div class="reason-input">' + (('textfield' === inputType) ? '<input type="text" />' : '<textarea rows="5"></textarea>') + '</div>';

                    _parent.append($(reasonInputHtml));
                    _parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                }
            });

            // If the user has clicked outside the window, cancel it.
            $modal.on('click', function (evt) {
                var $target = $(evt.target);

                // If the user has clicked anywhere in the modal dialog, just return.
                if ($target.hasClass('ts-modal-body') || $target.hasClass('ts-modal-footer')) {
                    return;
                }

                // If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
                if (!$target.hasClass('button-close') && ($target.parents('.ts-modal-body').length > 0 || $target.parents('.ts-modal-footer').length > 0)) {
                    return;
                }

                closeModal();
            });
        }

        function showModal() {
            resetModal();

            // Display the dialog box.
            $modal.addClass('active');

            $('body').addClass('has-ts-modal');
        }

        function closeModal() {
            $modal.removeClass('active');

            $('body').removeClass('has-ts-modal');
        }

        function resetModal() {
            if (TSCustomReasons.hasOwnProperty(currentPluginName) === true) {
                $modal.find("ul#reasons-list").html(TSCustomReasons[currentPluginName]);
            } else {
                $modal.find("ul#reasons-list").html(reasonsHtml);

            }
            var defaultSelect = TSDefaultReason[currentPluginName];
            $modal.find('.button').removeClass('disabled');

            // Remove all input fields ( textfield, textarea ).
            $modal.find('.reason-input').remove();

            var $deactivateButton = $modal.find('.button-deactivate');
            $modal.find(".reason-hide").hide();
            /*
             * If the modal dialog has no confirmation message, that is, it has only one panel, then ensure
             * that clicking the deactivate button will actually deactivate the plugin.
             */
            if ($modal.hasClass('no-confirmation-message')) {
                $deactivateButton.addClass('allow-deactivate');
                showPanel('reasons');
            }
        }

        function showPanel(panelType) {
            $modal.find('.ts-modal-panel').removeClass('active ');
            $modal.find('[data-panel-id="' + panelType + '"]').addClass('active');

            updateButtonLabels();
        }

        function updateButtonLabels() {
            var $deactivateButton = $modal.find('.button-deactivate');
            
            // Reset the deactivate button's text.
            if ('confirm' === getCurrentPanel()) {
                $deactivateButton.text('<?php printf( WCDN_TS_deactivate::load_str( 'deactivation-modal-button-confirm' ) ); ?>');
            } else {
                var $radio = $('input[type="radio"]:checked');
                if( $radio.length == 0 ) {
                    $deactivateButton.text('<?php printf( WCDN_TS_deactivate::load_str( 'deactivation-modal-button-deactivate' ) ); ?>');
                } else {
                    var _parent = $( $radio ).parents('li:first');
                    var _parent_ul = $( $radio ).parents('ul#reasons-list');

                    _parent_ul.children("li.li-active").removeClass("li-active");

                    $modal.find('.reason-input').remove();
                    $modal.find('.button-deactivate').text('<?php printf( WCDN_TS_deactivate::load_str( 'deactivation-modal-button-submit' ) ); ?>');

                    if (_parent.hasClass('has_html')) {
                        _parent.addClass('li-active');
                    }
                    
                    if (_parent.hasClass('has-input')) {
                        var inputType = _parent.data('input-type'),
                            inputPlaceholder = _parent.data('input-placeholder'),
                            reasonInputHtml = '<div class="reason-input">' + (('textfield' === inputType) ? '<input type="text" />' : '<textarea rows="5"></textarea>') + '</div>';

                        _parent.append($(reasonInputHtml));
                        _parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                    }
                }
            }
        }

        function getCurrentPanel() {
            return $modal.find('.ts-modal-panel.active').attr('data-panel-id');
        }
    })(jQuery);
</script>
