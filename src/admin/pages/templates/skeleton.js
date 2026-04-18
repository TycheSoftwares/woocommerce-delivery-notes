function Skeleton() {
    return (
        <div className="wcdn-template-skeleton">
            <div className="wcdn-template-sidebar">
                <div className="wcdn-skeleton-card">
                    <div className="wcdn-skeleton-heading"></div>
                    <div className="wcdn-skeleton-line"></div>
                    <div className="wcdn-skeleton-input"></div>
                    <div className="wcdn-skeleton-line"></div>
                    <div className="wcdn-skeleton-input"></div>
                    <div className="wcdn-skeleton-checkbox"></div>
                    <div className="wcdn-skeleton-checkbox"></div>
                </div>

                <div className="wcdn-skeleton-card">
                    <div className="wcdn-skeleton-heading"></div>

                    {[1, 2, 3, 4].map((i) => (
                        <div key={i} className="wcdn-skeleton-group">
                            <div className="wcdn-skeleton-line short"></div>
                            <div className="wcdn-skeleton-input"></div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="wcdn-template-preview">
                <div className="wcdn-preview-card">
                    <div className="wcdn-skeleton-logo"></div>
                    <div className="wcdn-skeleton-title"></div>
                    <div className="wcdn-skeleton-divider"></div>
                    <div className="wcdn-skeleton-address-row">
                        <div className="wcdn-skeleton-address"></div>
                        <div className="wcdn-skeleton-address"></div>
                    </div>

                    <div className="wcdn-skeleton-divider"></div>
                    <div className="wcdn-skeleton-table">
                        <div className="wcdn-skeleton-table-header"></div>

                        {[1, 2, 3].map((i) => (
                            <div key={i} className="wcdn-skeleton-table-row"></div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Skeleton;
