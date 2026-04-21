import { __, sprintf } from "@wordpress/i18n";
import { Button, Spinner } from "@wordpress/components";
import { useState, useEffect, useRef } from "@wordpress/element";
import { FormSection } from "@admin/components/form";
import { TEXT_DOMAIN } from "../../constants";
import { fetchStatus, uploadFont, deleteFont } from "../../api/fonts";
import { toast } from "../../utils/toast";

function FontUploadRow({ label, weight, file, onUpload, onDelete, isUploading }) {
    const inputRef = useRef(null);

    const formatBytes = (bytes) => {
        if (!bytes) return "";
        const kb = bytes / 1024;
        return kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb.toFixed(0)} KB`;
    };

    return (
        <div className="wcdn-font-row">
            <div className="wcdn-font-row__label">{label}</div>

            {file ? (
                <div className="wcdn-font-row__status wcdn-font-row__status--present">
                    <span className="wcdn-font-row__filename">
                        {file.name}
                        <em className="wcdn-font-row__size"> ({formatBytes(file.size)})</em>
                    </span>
                    <Button
                        variant="tertiary"
                        isDestructive
                        onClick={() => onDelete(weight)}
                        disabled={isUploading}
                    >
                        {__("Remove", TEXT_DOMAIN)}
                    </Button>
                </div>
            ) : (
                <div className="wcdn-font-row__status wcdn-font-row__status--missing">
                    <span className="wcdn-font-row__missing">
                        {__("No font uploaded", TEXT_DOMAIN)}
                    </span>
                    <Button
                        variant="secondary"
                        onClick={() => inputRef.current?.click()}
                        disabled={isUploading}
                    >
                        {isUploading
                            ? __("Uploading…", TEXT_DOMAIN)
                            : __("Upload font", TEXT_DOMAIN)}
                        {isUploading && <Spinner />}
                    </Button>
                    <input
                        ref={inputRef}
                        type="file"
                        accept=".ttf,.otf"
                        style={{ display: "none" }}
                        onChange={(e) => {
                            const f = e.target.files?.[0];
                            if (f) onUpload(f, weight);
                            e.target.value = "";
                        }}
                    />
                </div>
            )}
        </div>
    );
}

function FontSettings() {
    const [fontStatus, setFontStatus] = useState(null);
    const [uploading, setUploading] = useState(null);

    useEffect(() => {
        let mounted = true;

        fetchStatus()
            .then((data) => {
                if (mounted) setFontStatus(data);
            })
            .catch(() => {
                if (mounted) toast.error(__("Failed to load font status.", TEXT_DOMAIN));
            });

        return () => {
            mounted = false;
        };
    }, []);

    const handleUpload = async (file, weight) => {
        setUploading(weight);
        try {
            const result = await uploadFont(file, weight);
            setFontStatus(result.status);
            toast.success(__("Font uploaded successfully.", TEXT_DOMAIN));
        } catch (err) {
            toast.error(err || __("Failed to upload font.", TEXT_DOMAIN));
        } finally {
            setUploading(null);
        }
    };

    const handleDelete = async (weight) => {
        try {
            const result = await deleteFont(weight);
            setFontStatus(result.status);
            toast.success(__("Font removed.", TEXT_DOMAIN));
        } catch (err) {
            toast.error(err || __("Failed to remove font.", TEXT_DOMAIN));
        }
    };

    if (!fontStatus) {
        return (
            <div className="wcdn-font-settings">
                <Spinner />
            </div>
        );
    }

    if (!fontStatus.needed) {
        return (
            <div className="wcdn-font-settings">
                <FormSection title={__("Document Fonts", TEXT_DOMAIN)}>
                    <p>
                        {__(
                            "Everything looks good! Your store's language is fully supported and PDF documents will be generated correctly without any extra setup.",
                            TEXT_DOMAIN
                        )}
                    </p>
                </FormSection>
            </div>
        );
    }

    return (
        <div className="wcdn-font-settings">
            <FormSection title={__("Document Fonts", TEXT_DOMAIN)}>
                <p>
                    {__("Your store is currently set to", TEXT_DOMAIN)}{" "}
                    <strong>{fontStatus.language}</strong>
                    {__(", which requires the", TEXT_DOMAIN)}{" "}
                    <strong>{fontStatus.display_name}</strong>{" "}
                    {__(
                        "font. Without it, characters may appear as boxes or blank spaces in your printed documents.",
                        TEXT_DOMAIN
                    )}
                </p>
                <p>
                    {__(
                        "Download the font for free from Google Fonts, then upload both the Regular and Bold versions below.",
                        TEXT_DOMAIN
                    )}{" "}
                    <a href={fontStatus.google_url} target="_blank" rel="noreferrer">
                        {sprintf(
                            /* translators: %s: font family name */
                            __("Download %s →", TEXT_DOMAIN),
                            fontStatus.display_name
                        )}
                    </a>
                </p>

                <div className="wcdn-font-rows">
                    <FontUploadRow
                        label={__("Regular", TEXT_DOMAIN)}
                        weight="regular"
                        file={fontStatus.regular}
                        onUpload={handleUpload}
                        onDelete={handleDelete}
                        isUploading={uploading === "regular"}
                    />
                    <FontUploadRow
                        label={__("Bold", TEXT_DOMAIN)}
                        weight="bold"
                        file={fontStatus.bold}
                        onUpload={handleUpload}
                        onDelete={handleDelete}
                        isUploading={uploading === "bold"}
                    />
                </div>
            </FormSection>
        </div>
    );
}

export default FontSettings;
