import React, { useEffect, useState } from 'react';
import { Button, Skeleton, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faStar, faCheck, faLock } from '@fortawesome/free-solid-svg-icons';
import { __ } from '@wordpress/i18n';
import Header from '../components/Header';
import { _admin } from '../components/adminData';
import { moduleIcon } from '../components/icons';
import * as api from '../services/api';
import { applyFilters } from '../extend';

/** What ShopGlut Pro adds, module by module (from each module's pro_features). */
function ProPage() {
  const [modules, setModules] = useState(null);
  useEffect(() => {
    api.getModules().then((d) => setModules(d.modules)).catch((e) => message.error(e.message));
  }, []);

  return (
    <div className="sg-page">
      <Header />
      <div className="sg-body">
        <section className="sg-hero sg-hero-pro">
          <div>
            <h1 className="sg-hero-title"><FontAwesomeIcon icon={faStar} /> {__( 'ShopGlut Pro', 'shopglut' )}</h1>
            <p className="sg-hero-text">{__( 'More templates for every builder, Pro-only modules and advanced features — added on top of the free plugin, nothing to migrate.', 'shopglut' )}</p>
            <div className="sg-hero-actions">
              <Button type="primary" size="large" href={_admin.pro_url} target="_blank">{__( 'Get ShopGlut Pro', 'shopglut' )}</Button>
            </div>
          </div>
        </section>
        {!modules ? <Skeleton active /> : (
          <div className="sg-grid">
            {modules.filter((m) => m.pro_features.length || m.pro).map((m) => (
              <div key={m.id} className="sg-card">
                <div className="sg-card-head">
                  <span className="sg-module-icon"><FontAwesomeIcon icon={moduleIcon(m.icon)} /></span>
                  <div className="sg-card-title">{m.title}</div>
                  {m.pro ? <span className="sg-pill-pro"><FontAwesomeIcon icon={faLock} /> {__( 'Pro module', 'shopglut' )}</span> : null}
                </div>
                <ul className="sg-feature-list sg-feature-pro">
                  {(m.pro_features.length ? m.pro_features : m.features).map((f) => <li key={f}><FontAwesomeIcon icon={m.pro ? faCheck : faStar} />{f}</li>)}
                </ul>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

/**
 * Page shell for the Pro features page and add-on pages. The page slug picks the component an add-on
 * registered with the `shopglut.adminPages` filter: { 'shopglut-reports': { component } }.
 */
export default function AddonPage() {
  const slug = _admin.page || '';
  if (slug === 'shopglut-pro') return <ProPage />;

  const page = applyFilters('shopglut.adminPages', {})[slug];
  if (!page || !page.component) {
    return (
      <div className="sg-page">
        <Header />
        <div className="sg-body" style={{ paddingTop: 60, textAlign: 'center' }}>
          {__( 'This page belongs to an add-on that is not active.', 'shopglut' )}
        </div>
      </div>
    );
  }
  const Page = page.component;
  return <Page slug={slug} Header={Header} />;
}
