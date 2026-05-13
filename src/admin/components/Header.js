import { __ } from "@wordpress/i18n";
import { Card, __experimentalHStack as HStack } from "@wordpress/components";
import { NavLink } from "react-router-dom";

function Header() {
	return (
		<div id="wcdn-header">
			<Card className="wcdn-topbar" isBorderless>
				<h1>{__("Print Invoices & Delivery Notes", "woocommerce-delivery-notes")}</h1>
				<p>{__("Generate, customize, print, and email order documents.", "woocommerce-delivery-notes")}</p>
			</Card>

			<Card className="wcdn-navigation" isBorderless>
				<HStack align={"end"} justify="left" spacing={5}>
					<HStack justify="left" className="wcdn-navigation-menu">
						{[
							{ name: __("Dashboard", "woocommerce-delivery-notes"), path: "/" },
							{
								name: __("Templates", "woocommerce-delivery-notes"),
								path: "/templates",
							},
							{
								name: __("Settings", "woocommerce-delivery-notes"),
								path: "/settings",
							},
							{ name: __("FAQs", "woocommerce-delivery-notes"), path: "/faqs" },
						].map((item, i) => (
							<NavLink
								key={i}
								to={item.path}
								className={({ isActive }) => (isActive ? "is-active" : "")}
							>
								{item.name}
							</NavLink>
						))}
					</HStack>
				</HStack>
			</Card>
		</div>
	);
}

export default Header;
