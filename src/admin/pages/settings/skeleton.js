import {
    Card,
    CardHeader,
    CardBody,
    __experimentalHeading as Heading,
} from "@wordpress/components";

function Skeleton() {
    return (
        <div className="wcdn-settings-skeleton">
            <Card className="wcdn-card">
                <CardHeader>
                    <Heading level={4}>
                        <div className="wcdn-skeleton-line wcdn-skeleton-title" />
                    </Heading>
                </CardHeader>

                <CardBody>
                    <div className="wcdn-skeleton-line" />
                    <div className="wcdn-skeleton-line" />
                    <div className="wcdn-skeleton-line" />
                    <div className="wcdn-skeleton-line" />
                </CardBody>
            </Card>

            <Card className="wcdn-card">
                <CardHeader>
                    <Heading level={4}>
                        <div className="wcdn-skeleton-line wcdn-skeleton-title" />
                    </Heading>
                </CardHeader>

                <CardBody>
                    <div className="wcdn-skeleton-line" />
                    <div className="wcdn-skeleton-line" />
                    <div className="wcdn-skeleton-line" />
                </CardBody>
            </Card>
        </div>
    );
}

export default Skeleton;
