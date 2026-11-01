import React from 'react';
import { __ } from '@wordpress/i18n';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { _admin, _modules } from './adminData';
import { moduleIcon } from './icons';
import { applyFilters } from '../extend';

/**
 * Title row of a module page, with its tabs (Layouts | Settings | tabs added by add-ons).
 */
export default function ModuleHeader({ module, description = '', actions = null }) {
  const m = _modules[module] || { title: module };
  const tab = _admin.tab === 'settings' || !m.layouts ? 'settings' : 'layouts';

  const tabs = applyFilters('shopglut.module.tabs', [
    ...(m.layouts ? [{ key: 'layouts', label: m.layouts_label || __( 'Layouts', 'shopglut' ), href: m.url }] : []),
    ...(m.has_settings ? [{ key: 'settings', label: __( 'Settings', 'shopglut' ), href: m.settings_url }] : []),
  ], { module });

  return (
    <div className="sg-module-head">
      <div className="sg-module-title">
        <span className="sg-module-icon"><FontAwesomeIcon icon={moduleIcon(m.icon)} /></span>
        <div>
          <h1 className="sg-title">{m.title}</h1>
          {description ? <p className="sg-subtitle">{description}</p> : null}
        </div>
      </div>
      <div className="sg-module-side">
        {tabs.length > 1 ? (
          <div className="sg-tabs">
            {tabs.map((t) => <a key={t.key} href={t.href} className={t.key === tab ? 'active' : ''}>{t.label}</a>)}
          </div>
        ) : null}
        {actions}
      </div>
    </div>
  );
}
