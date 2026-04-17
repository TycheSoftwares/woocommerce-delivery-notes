import { useState, useRef } from "@wordpress/element";
import { Icon } from "@wordpress/components";
import { chevronDown, chevronUp } from "@wordpress/icons";
import { Checkbox } from "@admin/components/form";

function SectionItem({ label = "", checked = false, disabled, onToggle = {}, children }) {
    const [open, setOpen] = useState(false);
    const sectionRef = useRef(null);

    const handleClick = () => {
        const next = !open;
        const y = sectionRef.current.getBoundingClientRect().top + window.scrollY - 120;
        setOpen(next);

        if (!open && sectionRef.current) {
            setTimeout(() => {
                sectionRef.current.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                    top: y,
                });
            }, 100);
        }
    };

    return (
        <div ref={sectionRef} className="wcdn-template-settings-item wcdn-section-item">
            {"" !== label && (
                <div className="wcdn-section-header">
                    <div className="wcdn-section-title">
                        <Checkbox
                            label={label}
                            checked={checked}
                            onChange={onToggle}
                            disabled={disabled}
                        />
                    </div>

                    {children && children.length > 0 && (
                        <button
                            type="button"
                            className="wcdn-customize-toggle"
                            onClick={handleClick}
                        >
                            Customize
                            <Icon icon={open ? chevronUp : chevronDown} />
                        </button>
                    )}
                </div>
            )}

            {checked && children && open && <div className="wcdn-section-content">{children}</div>}
            {"" === label && !checked && children}
        </div>
    );
}

export default SectionItem;
