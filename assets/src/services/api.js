/**
 * ShopGlut API service: every call of the admin app to WordPress (admin-ajax, nonce shopglut_admin).
 */

const { ajax_url, nonce } = window.shopglut_admin || {};

const encode = (value) => (typeof value === 'object' ? JSON.stringify(value) : value);

/** Call a ShopGlut admin AJAX action. Add-ons use it for their own actions (shopglut_pro_…). */
export async function request(action, data = {}, method = 'GET') {
  if (!ajax_url || !nonce) {
    throw new Error('ShopGlut admin data not loaded.');
  }

  const entries = Object.entries(data).filter(([, v]) => v !== undefined && v !== null);
  let url = ajax_url;
  const options = { method, credentials: 'same-origin' };

  if (method === 'GET') {
    const params = new URLSearchParams({ action, nonce });
    entries.forEach(([k, v]) => params.append(k, encode(v)));
    url += '?' + params.toString();
  } else {
    const body = new FormData();
    body.append('action', action);
    body.append('nonce', nonce);
    entries.forEach(([k, v]) => body.append(k, encode(v)));
    options.body = body;
  }

  const response = await fetch(url, options);
  let result;
  try {
    result = await response.json();
  } catch (e) {
    throw new Error('The server sent an invalid response.');
  }
  if (!result.success) {
    const err = new Error((result.data && result.data.message) || 'Request failed.');
    err.data = result.data;
    throw err;
  }
  return result.data;
}

const get = (action, data) => request('shopglut_' + action, data, 'GET');
const post = (action, data) => request('shopglut_' + action, data, 'POST');

/* ── Dashboard & modules ─────────────────────────────────────────── */

export const getDashboard = () => get('get_dashboard');
export const getModules = () => get('get_modules');
export const toggleModule = (id, enabled) => post('toggle_module', { id, enabled: enabled ? 1 : 0 });

/* ── Layouts ─────────────────────────────────────────────────────── */

export const getLayouts = (module, params = {}) => get('get_layouts', { module, ...params });
export const createLayout = (module, template, name) => post('create_layout', { module, template, name });
export const updateLayout = (id, data) => post('update_layout', { id, ...data });
export const deleteLayout = (id) => post('delete_layout', { id });
export const duplicateLayout = (id) => post('duplicate_layout', { id });
export const bulkLayouts = (module, ids, bulk) => post('bulk_layouts', { module, ids: ids.join(','), bulk });

/* ── Tools ───────────────────────────────────────────────────────── */

export const exportData = (what) => get('export_data', { what: what.join(',') });
export const importData = (data) => post('import_data', { data });
export const getSystemStatus = () => get('system_status');
export const resetData = (what) => post('reset_data', { what });
