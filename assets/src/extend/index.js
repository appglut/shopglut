/**
 * The extension API of the ShopGlut admin app: what ShopGlut Pro (or any add-on) builds on.
 *
 * Every admin page imports this file first. It sets up `window.shopglut` and draws the page only
 * when the document has finished loading, so add-on scripts enqueued with the
 * `shopglut_admin_enqueue_scripts` action (depending on the page's script handle) have run and
 * registered their filters by then.
 *
 * Add-ons register with WordPress hooks (`wp.hooks`, or `window.shopglut.hooks`):
 *
 *   shopglut.hooks.addFilter( 'shopglut.dashboard.cards', 'shopglut-pro', ( cards ) => [ ...cards, { … } ] );
 *
 * React components must use ShopGlut's own React (`window.shopglut.React`), not wp.element, because
 * they are drawn inside ShopGlut's React tree. Every filter name is listed in EXTENDING.md.
 */
import React from 'react';
import * as ReactDOMClient from 'react-dom/client';
// The antd components ShopGlut itself uses (not the whole library, which would add megabytes to every page).
import {
  Button, Card, Checkbox, Col, Drawer, Dropdown, Empty, Input, InputNumber, message, Modal, notification,
  Popconfirm, Progress, Radio, Result, Row, Segmented, Select, Skeleton, Space, Spin, Switch, Table, Tabs, Tag, Tooltip,
} from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { __, _x, _n, sprintf } from '@wordpress/i18n';

const antd = {
  Button, Card, Checkbox, Col, Drawer, Dropdown, Empty, Input, InputNumber, message, Modal, notification,
  Popconfirm, Progress, Radio, Result, Row, Segmented, Select, Skeleton, Space, Spin, Switch, Table, Tabs, Tag, Tooltip,
};

export const API_VERSION = 1;

/** Small stand-in for @wordpress/hooks when wp.hooks is missing. */
function createHooks() {
  const store = { filters: {}, actions: {} };
  const add = (type) => (name, namespace, callback, priority = 10) => {
    (store[type][name] = store[type][name] || []).push({ namespace, callback, priority });
    store[type][name].sort((a, b) => a.priority - b.priority);
  };
  const remove = (type) => (name, namespace) => {
    store[type][name] = (store[type][name] || []).filter((h) => h.namespace !== namespace);
  };
  return {
    addFilter: add('filters'),
    removeFilter: remove('filters'),
    addAction: add('actions'),
    removeAction: remove('actions'),
    applyFilters: (name, value, ...args) => (store.filters[name] || []).reduce((v, h) => h.callback(v, ...args), value),
    doAction: (name, ...args) => (store.actions[name] || []).forEach((h) => h.callback(...args)),
    hasFilter: (name) => !!(store.filters[name] || []).length,
  };
}

const hooks = (typeof window !== 'undefined' && window.wp && window.wp.hooks) || createHooks();

/** Apply a filter; a callback that throws is skipped so one broken add-on cannot blank the page. */
export function applyFilters(name, value, ...args) {
  try {
    return hooks.applyFilters(name, value, ...args);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('[ShopGlut] filter "' + name + '" failed:', e);
    return value;
  }
}

export function doAction(name, ...args) {
  try {
    hooks.doAction(name, ...args);
  } catch (e) {
    // eslint-disable-next-line no-console
    console.error('[ShopGlut] action "' + name + '" failed:', e);
  }
}

/** Whether ShopGlut Pro is active (PHP: shopglut_is_pro()). */
export const isPro = () => !!(typeof window !== 'undefined' && window.shopglut_admin && window.shopglut_admin.pro_enabled);

const api = (typeof window !== 'undefined' && window.shopglut && window.shopglut.apiVersion) ? window.shopglut : {
  apiVersion: API_VERSION,
  version: (typeof window !== 'undefined' && window.shopglut_admin && window.shopglut_admin.version) || '',
  hooks,
  applyFilters,
  doAction,
  isPro,
  React,
  ReactDOM: ReactDOMClient,
  antd,
  FontAwesomeIcon,
  i18n: { __, _x, _n, sprintf },
  /** Shared ShopGlut components and helpers, added by the modules that define them (see expose()). */
  components: {},
  utils: {},
};

if (typeof window !== 'undefined') {
  window.shopglut = api;
}

/** Make ShopGlut components or helpers available to add-ons as window.shopglut.components / utils. */
export function expose(components = {}, utils = {}) {
  Object.assign(api.components, components);
  Object.assign(api.utils, utils);
}

/**
 * Draw the page once add-on scripts have run: module scripts execute before DOMContentLoaded, in
 * the order they were printed. Classic add-on scripts can also queue a callback before ShopGlut
 * loads: (window.shopglutReady = window.shopglutReady || []).push( ( shopglut ) => { … } ).
 */
export function boot(render) {
  const start = () => {
    const queue = (typeof window !== 'undefined' && window.shopglutReady) || [];
    queue.forEach((cb) => { try { cb(api); } catch (e) { console.error('[ShopGlut] ready callback failed:', e); } }); // eslint-disable-line no-console
    if (typeof window !== 'undefined') {
      window.shopglutReady = { push: (cb) => { try { cb(api); } catch (e) { console.error(e); } } }; // eslint-disable-line no-console
    }
    doAction('shopglut.ready', api);
    render();
  };
  if (typeof document !== 'undefined' && document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }
}

export default api;
