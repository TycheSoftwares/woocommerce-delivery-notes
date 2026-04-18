import {
    Card,
    CardHeader,
    CardBody,
    __experimentalHeading as Heading,
} from "@wordpress/components";

function Skeleton() {
    return (
        <div className="wcdn-dashboard-skeleton">
            <Card className="wcdn-card">
                <CardHeader>
                    <Heading level={4}>
                        <div className="wcdn-skeleton-line wcdn-skeleton-title" />
                    </Heading>
                </CardHeader>

                <CardBody>
                    <div className="wcdn-skeleton-checklist">
                        {[1, 2, 3, 4].map((i) => (
                            <div key={i} className="wcdn-skeleton-check-row">
                                <div className="wcdn-skeleton-box" />
                                <div className="wcdn-skeleton-line wcdn-skeleton-text" />
                            </div>
                        ))}
                    </div>

                    <div className="wcdn-skeleton-progress" />
                    <div className="wcdn-skeleton-line wcdn-skeleton-small" />
                    <div className="wcdn-skeleton-line wcdn-skeleton-wide" />
                </CardBody>
            </Card>

            <Card className="wcdn-card">
                <CardHeader>
                    <Heading level={4}>
                        <div className="wcdn-skeleton-line wcdn-skeleton-title" />
                    </Heading>
                </CardHeader>

                <CardBody>
                    {[1, 2, 3].map((i) => (
                        <div key={i} className="wcdn-skeleton-button" />
                    ))}

                    <div className="wcdn-skeleton-notice" />
                </CardBody>
            </Card>
        </div>
    );
}

export default Skeleton;
