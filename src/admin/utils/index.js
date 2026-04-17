import { Fragment } from "@wordpress/element";

/**
 * Render items separated by a delimiter.
 *
 * Filters empty values and prevents duplicate separators.
 *
 * @param {Array} items
 * @param {string|JSX} separator
 * @return JSX
 */
export function separate(items = [], separator = " · ") {
    const parts = items.filter(Boolean);

    return parts.map((item, index) => (
        <Fragment key={index}>
            {index > 0 && separator}
            {item}
        </Fragment>
    ));
}
