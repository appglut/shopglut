/**
 * Data localized by PHP (shopglut_admin), with safe fallbacks.
 */
const admin = typeof window !== 'undefined' && window.shopglut_admin ? window.shopglut_admin : {};

export const _admin = admin;
export const _pg = admin.pages || {};
export const _modules = admin.modules || {};
export const _dashboard = admin.dashboard_url || '/wp-admin/';
export const _pluginUrl = admin.plugin_url || '';
export const _logo = () => _pluginUrl + 'global-assets/images/header-logo.svg';

/** Module list in display order. */
export const moduleList = () => Object.values(_modules);

/** Categories in display order. */
export const CATEGORIES = ['builders', 'product', 'cart', 'engagement'];
