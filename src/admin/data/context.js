import { createContext, useContext, useState, useEffect } from "@wordpress/element";
import { useWcdnData } from "./hooks";

const DataContext = createContext();

export function useData() {
    return useContext(DataContext);
}

const DataProvider = ({ children }) => {
    const [data, setData] = useState({});
    const [isDataLoaded, setIsDataLoaded] = useState(false);
    const [showLoader, setShowLoader] = useState(true);
    const { wcdnData, hasResolved } = useWcdnData();

    useEffect(() => {
        if (!hasResolved) {
            return;
        }

        if (wcdnData?.data) {
            setData(wcdnData.data);
        }

        setIsDataLoaded(true);
        setShowLoader(false);
    }, [hasResolved]);

    return (
        <DataContext.Provider
            value={{
                data,
                setData,
                showLoader,
                setShowLoader,
                isDataLoaded,
            }}
        >
            {children}
        </DataContext.Provider>
    );
};

export default DataProvider;
