import { Route, Routes } from "react-router-dom";
import { Dashboard, FAQs, Settings, Templates } from "./pages";

export const AppRoutes = () => {
    return (
        <Routes>
            <Route path="/" element={<Dashboard />} />
            <Route path="/faqs" element={<FAQs />} />
            <Route path="/settings" element={<Settings />} />
            <Route path="/templates" element={<Templates />} />
            {/* <Route path="*" element={<Navigate to="/" replace />} /> */}
        </Routes>
    );
};
