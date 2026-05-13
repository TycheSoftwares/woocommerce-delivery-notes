import { __ } from "@wordpress/i18n";
import { Button, Icon } from "@wordpress/components";
import { upload } from "@wordpress/icons";
import TooltipLabel from "./Tooltip";
import { useRef } from "@wordpress/element";
import { TEXT_DOMAIN } from "../../constants";

function MediaUpload({ label, tooltip, value, onChange, help, className }) {
    const frameRef = useRef(null);
    const openMediaLibrary = () => {
        if (!frameRef.current) {
            frameRef.current = wp.media({
                title: __("Select Logo", TEXT_DOMAIN),
                button: {
                    text: __("Use Logo", TEXT_DOMAIN),
                },
                library: {
                    type: "image",
                },
                multiple: false,
            });

            frameRef.current.on("select", () => {
                const attachment = frameRef.current.state().get("selection").first().toJSON();
                onChange(attachment.url);
            });
        }

        frameRef.current.open();
    };

    return (
        <div className={`wcdn-logo-upload ${className}`}>
            <TooltipLabel label={label} tooltip={tooltip} />

            <div className="wcdn-logo-row">
                <div className="wcdn-logo-preview">
                    {value ? (
                        <img
                            src={value}
                            alt=""
                            style={{
                                maxWidth: "100%",
                                maxHeight: "100%",
                            }}
                        />
                    ) : (
                        __("Logo", TEXT_DOMAIN)
                    )}
                </div>

                <div className="wcdn-logo-actions">
                    <Button
                        variant="secondary"
                        onClick={openMediaLibrary}
                        className="wcdn-upload-button"
                    >
                        <Icon icon={upload} size={18} />
                        {__("Upload Logo", TEXT_DOMAIN)}
                    </Button>

                    {value && (
                        <Button variant="tertiary" onClick={() => onChange("")}>
                            {__("Remove Logo", TEXT_DOMAIN)}
                        </Button>
                    )}

                    {help && <div className="wcdn-logo-help">{help}</div>}
                </div>
            </div>
        </div>
    );
}

export default MediaUpload;
