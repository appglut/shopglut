import React, { useState } from 'react';
import { Drawer } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faGaugeHigh, faCubes, faGear, faToolbox, faStar, faHouse } from '@fortawesome/free-solid-svg-icons';
import { __ } from '@wordpress/i18n';
import { _admin, _pg, _dashboard, _logo, moduleList, CATEGORIES } from './adminData';
import { moduleIcon, categoryLabel } from './icons';
import { applyFilters } from '../extend';

/**
 * Three-bar button for the left of every header. Opens a side menu with the ShopGlut pages and every
 * enabled module, grouped by category.
 */
export default function NavMenu() {
  const [open, setOpen] = useState(false);
  const page = _admin.page || '';
  const current = _admin.module ? 'module:' + _admin.module : page;

  const main = applyFilters('shopglut.navMenu.items', [
    { key: 'shopglut', label: __( 'Dashboard', 'shopglut' ), icon: faGaugeHigh, href: _pg.dashboard },
    { key: 'shopglut-modules', label: __( 'Modules', 'shopglut' ), icon: faCubes, href: _pg.modules },
  ]);
  const footer = [
    { key: 'shopglut-settings', label: __( 'Global Settings', 'shopglut' ), icon: faGear, href: _pg.settings },
    { key: 'shopglut-tools', label: __( 'Tools', 'shopglut' ), icon: faToolbox, href: _pg.tools },
    ...(_pg.pro ? [{ key: 'shopglut-pro', label: __( 'Go Pro', 'shopglut' ), icon: faStar, href: _pg.pro, pro: true }] : []),
  ];

  const enabled = moduleList().filter((m) => m.enabled && (m.layouts || m.has_settings));
  const link = (i) => (
    <a key={i.key} href={i.href} className={(i.key === current ? 'active' : '') + (i.pro ? ' sg-nav-pro' : '')}>
      <span className="ic"><FontAwesomeIcon icon={i.icon} /></span>
      {i.label}
    </a>
  );

  return (
    <>
      <button type="button" className="sg-hamburger" aria-label={__( 'Open menu', 'shopglut' )} aria-expanded={open} onClick={() => setOpen(true)}>
        <span /><span /><span />
      </button>
      <Drawer
        open={open}
        onClose={() => setOpen(false)}
        placement="left"
        width={290}
        title={<img src={_logo()} alt="ShopGlut" className="sg-drawer-logo" />}
        rootClassName="sg-nav-drawer"
        styles={{ body: { padding: '10px 0' }, header: { background: 'linear-gradient(135deg, #12101f 0%, #1e1638 55%, #2a1d52 100%)', borderBottom: 0 } }}
      >
        <nav className="sg-nav-list">
          {main.map(link)}
          {CATEGORIES.map((cat) => {
            const items = enabled.filter((m) => m.category === cat);
            if (!items.length) return null;
            return (
              <React.Fragment key={cat}>
                <div className="sg-nav-group">{categoryLabel(cat)}</div>
                {items.map((m) => link({ key: 'module:' + m.id, label: m.title, icon: moduleIcon(m.icon), href: m.url }))}
              </React.Fragment>
            );
          })}
          <div className="sg-nav-group" />
          {footer.map(link)}
        </nav>
        <div className="sg-nav-foot">
          <a href={_dashboard}><span className="ic"><FontAwesomeIcon icon={faHouse} /></span>{__( 'WordPress Dashboard', 'shopglut' )}</a>
        </div>
      </Drawer>
    </>
  );
}
