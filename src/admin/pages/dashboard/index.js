import { __, sprintf } from "@wordpress/i18n";
import {
    Card,
    CardHeader,
    CardBody,
    Button,
    Notice,
    CheckboxControl,
    __experimentalHeading as Heading,
    __experimentalVStack as VStack,
    ProgressBar,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { TEXT_DOMAIN } from "../../constants";
import { fetch as fetchData } from "../../api/dashboard";
import Skeleton from "./skeleton";
import { toast } from "../../utils/toast";

function Dashboard() {
    const [data, setData] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let mounted = true;

        fetchData()
            .then((data) => {
                if (!mounted) return;
                setData(data || null);
            })
            .catch((error) => {
                toast.error(error?.message || __("Failed to load Dashboard data", TEXT_DOMAIN));
            })
            .finally(() => {
                if (mounted) setIsLoading(false);
            });

        return () => {
            mounted = false;
        };
    }, []);

    if (isLoading || !data) {
        return <Skeleton />;
    }

    const checks = [
        data?.store_details_configured,
        data?.at_least_one_template_enabled,
        data?.pdf_generation_enabled,
        data?.invoice_numbering_configured,
    ];

    const completedCount = checks.filter(Boolean).length;
    const totalCount = checks.length;
    const progress = totalCount ? (completedCount / totalCount) * 100 : 0;

    let leftSideBottomText = "";
    const allTrue = checks.every(Boolean);
    const allFalse = checks.every((v) => !v);

    if (allTrue) {
        leftSideBottomText = __("You're ready to generate and print documents!", TEXT_DOMAIN);
    } else if (
        data?.store_details_configured &&
        data?.at_least_one_template_enabled &&
        data?.pdf_generation_enabled &&
        !data?.invoice_numbering_configured
    ) {
        leftSideBottomText = __(
            "Your setup is almost ready. Complete the remaining steps to start generating documents.",
            TEXT_DOMAIN
        );
    } else if (allFalse) {
        leftSideBottomText = __(
            "Complete these steps to start printing and email order documents.",
            TEXT_DOMAIN
        );
    } else {
        leftSideBottomText = __(
            "Complete the remaining steps to start generating documents.",
            TEXT_DOMAIN
        );
    }

    return (
        <div className="wcdn-dashboard-content">
            <Card isRounded={false} className="wcdn-card">
                <CardHeader className="wcdn-card-header">
                    <Heading level={4}>{__("Getting Started", TEXT_DOMAIN)}</Heading>
                </CardHeader>
                <CardBody>
                    <CheckboxControl
                        className="wcdn-checkbox"
                        disabled={true}
                        label={__("Store details configured", TEXT_DOMAIN)}
                        checked={data?.store_details_configured}
                    />

                    <CheckboxControl
                        className="wcdn-checkbox"
                        disabled={true}
                        label={__("At least one template enabled", TEXT_DOMAIN)}
                        checked={data?.at_least_one_template_enabled}
                    />

                    <CheckboxControl
                        className="wcdn-checkbox"
                        disabled={true}
                        label={__("PDF generation enabled", TEXT_DOMAIN)}
                        checked={data?.pdf_generation_enabled}
                    />

                    <CheckboxControl
                        className="wcdn-checkbox"
                        disabled={true}
                        label={__("Invoice numbering configured", TEXT_DOMAIN)}
                        checked={data?.invoice_numbering_configured}
                    />

                    <ProgressBar value={progress} max={100} className="wcdn-progress-bar" />
                    <p className="wcdn-progress-bar-text">
                        {sprintf(
                            /* translators: 1: completed steps, 2: total steps */
                            __("%1$d of %2$d steps completed", TEXT_DOMAIN),
                            completedCount,
                            totalCount
                        )}
                    </p>
                    <p>{leftSideBottomText}</p>
                </CardBody>
            </Card>

            <Card isRounded={false} className="wcdn-card">
                <CardHeader className="wcdn-card-header">
                    <Heading level={4}>{__("Quick Actions", TEXT_DOMAIN)}</Heading>
                </CardHeader>

                <CardBody>
                    <VStack spacing={4}>
                        <Button
                            variant="primary"
                            isLarge
                            className="wcdn-quick-action"
                            onClick={() => {
                                window.open(
                                    data?.url?.preview_sample_invoice,
                                    "_blank",
                                    "noopener,noreferrer"
                                );
                            }}
                            disabled={!data?.url?.preview_sample_invoice}
                        >
                            {__("Preview Sample Document", TEXT_DOMAIN)}
                        </Button>

                        <Button
                            variant="primary"
                            isLarge
                            className="wcdn-quick-action"
                            onClick={() => {
                                window.open(
                                    data?.url?.view_latest_order_invoice,
                                    "_blank",
                                    "noopener,noreferrer"
                                );
                            }}
                            disabled={!data?.url?.view_latest_order_invoice}
                        >
                            {__("View Latest Order Invoice", TEXT_DOMAIN)}
                        </Button>

                        <Button
                            variant="primary"
                            isLarge
                            className="wcdn-quick-action"
                            onClick={() => {
                                window.location.href = data?.url?.edit_templates;
                            }}
                            disabled={!data?.url?.view_latest_order_invoice}
                        >
                            {__("Edit Templates", TEXT_DOMAIN)}
                        </Button>

                        <Notice
                            status="info"
                            isDismissible={false}
                            className="wcdn-quick-actions-notice"
                        >
                            {__(
                                "You can customize documents anytime to match your store.",
                                TEXT_DOMAIN
                            )}
                        </Notice>
                    </VStack>
                </CardBody>
            </Card>
        </div>
    );
}

export default Dashboard;
