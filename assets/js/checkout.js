const geidea_data = window.wc.wcSettings.getSetting('geidea_data', {});
const title = window.wp.i18n.__(geidea_data.title || 'Geidea', 'geidea');
const geidea_label = window.wp.element.createElement(
    'div',
    { style: { width: "95%", display: "flex", flexDirection: "row", justifyContent: "space-between", gap: "0.5rem" } },
    window.wp.element.createElement(
        'div',
        {},
        title
    ),
    window.wp.element.createElement(
        'img',
        { src: geidea_data.logo_url, alt: 'geidea' }
    )
);
const geidea_content = window.wp.element.createElement('div', {}, geidea_data.description || "Pay with Geidea!");

const Block_Gateway = {
    name: 'geidea',
    label: geidea_label,
    content: geidea_content,
    edit: geidea_content,
    canMakePayment: () => true, // Function to check if payment can be made
    ariaLabel: title, // Accessible label
    supports: {
        showSavedCards: geidea_data.cardOnFile === 'yes',
        showSaveOption: geidea_data.cardOnFile === 'yes',
        features: geidea_data.supports
    }
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);