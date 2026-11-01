import React from 'react';
import { __ } from '@wordpress/i18n';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHouse, faChevronLeft } from '@fortawesome/free-solid-svg-icons';
import { _admin, _pg, _dashboard, _logo } from './adminData';
import NavMenu from './NavMenu';
import { applyFilters } from '../extend';
import './header.css';

/**
 * Top bar of every ShopGlut page: menu button, back to WordPress, logo, main tabs and a slot on the
 * right for page actions.
 */
export default function Header({ actions = null }) {
  const page = _admin.page || '';
  const items = applyFilters('shopglut.header.items', [
    { key: 'shopglut', label: __( 'Dashboard', 'shopglut' ), href: _pg.dashboard },
    { key: 'shopglut-modules', label: __( 'Modules', 'shopglut' ), href: _pg.modules },
    { key: 'shopglut-settings', label: __( 'Global Settings', 'shopglut' ), href: _pg.settings },
    { key: 'shopglut-tools', label: __( 'Tools', 'shopglut' ), href: _pg.tools },
    ...(_pg.pro ? [{ key: 'shopglut-pro', label: __( '⭐ Go Pro', 'shopglut' ), href: _pg.pro, pro: true }] : []),
  ]);

  return (
    <header className="sg-header">
      <div className="sg-header-left">
        <NavMenu />
        <a href={_dashboard} className="sg-header-back" title={__( 'Back to WordPress Admin', 'shopglut' )}>
          <FontAwesomeIcon icon={faChevronLeft} />
          <FontAwesomeIcon icon={faHouse} />
        </a>
        <img src={_logo()} alt="ShopGlut" className="sg-logo-img" />
      </div>
      <nav className="sg-header-nav">
        {items.map((n) => (
          <a key={n.key} href={n.href} className={(n.key === page ? 'active' : '') + (n.pro ? ' sg-nav-pro' : '')}>{n.label}</a>
        ))}
      </nav>
      <div className="sg-header-right">{actions}</div>
    </header>
  );
}
