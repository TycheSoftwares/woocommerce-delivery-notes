import { registerEntities } from "./data/entities";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { Header, Toast, Loader, Footer } from "./components";
import { useData } from "./data/context";
import { AppRoutes } from "./routes";

registerEntities();

function App() {
    const { isDataLoaded } = useData();

    return (
        <>
            {isDataLoaded && (
                <>
                    <Header />
                    <VStack className="wcdn-page">
                        <AppRoutes />
                    </VStack>
                    <Toast />
                    <Loader />
                    <Footer />
                </>
            )}
        </>
    );
}

export default App;
