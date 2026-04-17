import { Card, CardHeader, CardBody } from "@wordpress/components";

function FormSection({ title, description, children, className }) {
    return (
        <Card className={`wcdn-card mt-30 ${className}`} isRounded={false}>
            <CardHeader className="wcdn-card-header">
                <h4>{title}</h4>
                {description && <p className="wcdn-section-description">{description}</p>}
            </CardHeader>

            <CardBody className="wcdn-section-card">{children}</CardBody>
        </Card>
    );
}

export default FormSection;
