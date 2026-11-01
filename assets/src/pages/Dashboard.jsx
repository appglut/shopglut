import React, { useEffect, useState } from 'react';
import { Button, Skeleton, Tag, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowRight, faCubes, faLayerGroup, faCircleCheck, faBoxOpen, faStar, faGear, faToolbox } from '@fortawesome/free-solid-svg-icons';
import { __, sprintf } from '@wordpress/i18n';
import Header from '../components/Header';
import { _admin, _pg } from '../components/adminData';
import { moduleIcon } from '../components/icons';
import * as api from '../services/api';
import { applyFilters } from '../extend';

function Stat({ icon, label, value, hint }) {
  return (
    <div className="sg-stat">
      <span className="sg-stat-icon"><FontAwesomeIcon icon={icon} /></span>
      <div>
        <div className="sg-stat-value">{value}</div>
        <div className="sg-stat-label">{label}</div>
        {hint ? <div className="sg-stat-hint">{hint}</div> : null}
      </div>
    </div>
  );
}

export default function Dashboard() {
  const [data, setData] = useState(null);

  useEffect(() => {
    api.getDashboard().then(setData).catch((e) => message.error(e.message));
  }, []);

  const stats = data ? data.stats : null;
  const builders = data ? data.modules.filter((m) => m.layouts) : [];
  const others = data ? data.modules.filter((m) => !m.layouts) : [];

  const cards = applyFilters('shopglut.dashboard.cards', [], { data });

  return (
    <div className="sg-page">
      <Header />
      <div className="sg-body">
        <section className="sg-hero">
          <div>
            <h1 className="sg-hero-title">{__( 'Build your WooCommerce store', 'shopglut' )}</h1>
            <p className="sg-hero-text">{__( 'Product pages, shop grids, cart, checkout, thank you page, swatches, badges, filters, wishlist and side cart — one plugin, one place.', 'shopglut' )}</p>
            <div className="sg-hero-actions">
              <Button type="primary" size="large" href={_pg.modules}>{__( 'Manage modules', 'shopglut' )}</Button>
              {_pg.pro ? <Button size="large" ghost href={_pg.pro} icon={<FontAwesomeIcon icon={faStar} />}>{__( 'See Pro features', 'shopglut' )}</Button> : null}
            </div>
          </div>
          <div className="sg-hero-version">
            <Tag color="purple">v{_admin.version}</Tag>
            {_admin.pro_enabled ? <Tag color="gold">{__( 'Pro active', 'shopglut' )}</Tag> : null}
          </div>
        </section>

        <div className="sg-stats">
          {stats ? (
            <>
              <Stat icon={faCubes} label={__( 'Modules on', 'shopglut' )} value={`${stats.modules_enabled} / ${stats.modules_total}`} />
              <Stat icon={faLayerGroup} label={__( 'Layouts', 'shopglut' )} value={stats.layouts} />
              <Stat icon={faCircleCheck} label={__( 'Active layouts', 'shopglut' )} value={stats.layouts_active} />
              <Stat icon={faBoxOpen} label={__( 'Published products', 'shopglut' )} value={stats.products} hint={data.wc.version ? sprintf( __( 'WooCommerce %s', 'shopglut' ), data.wc.version ) : ''} />
            </>
          ) : [0, 1, 2, 3].map((i) => <div key={i} className="sg-stat"><Skeleton active paragraph={false} /></div>)}
        </div>

        {cards.length ? <div className="sg-grid sg-grid-cards">{cards}</div> : null}

        <h2 className="sg-section-title">{__( 'Builders', 'shopglut' )}</h2>
        <div className="sg-grid">
          {!data ? [0, 1, 2].map((i) => <div key={i} className="sg-card"><Skeleton active /></div>) : builders.map((m) => (
            <a key={m.id} href={m.enabled ? (_admin.modules[m.id] || {}).url : _pg.modules} className={'sg-card sg-card-link' + (m.enabled ? '' : ' is-off')}>
              <div className="sg-card-head">
                <span className="sg-module-icon"><FontAwesomeIcon icon={moduleIcon(m.icon)} /></span>
                <div className="sg-card-title">{m.title}</div>
                {!m.enabled ? <Tag>{__( 'Off', 'shopglut' )}</Tag> : null}
              </div>
              <p className="sg-card-text">{m.description}</p>
              <div className="sg-card-foot">
                <span>{sprintf( __( '%1$d layouts · %2$d active', 'shopglut' ), m.layout_count, m.active_count )}</span>
                <FontAwesomeIcon icon={faArrowRight} />
              </div>
            </a>
          ))}
        </div>

        <h2 className="sg-section-title">{__( 'Store features', 'shopglut' )}</h2>
        <div className="sg-grid">
          {data ? others.map((m) => (
            <a key={m.id} href={m.enabled && m.has_settings ? (_admin.modules[m.id] || {}).url : _pg.modules} className={'sg-card sg-card-link' + (m.enabled ? '' : ' is-off')}>
              <div className="sg-card-head">
                <span className="sg-module-icon"><FontAwesomeIcon icon={moduleIcon(m.icon)} /></span>
                <div className="sg-card-title">{m.title}</div>
                {m.pro && !m.available ? <Tag color="gold">Pro</Tag> : !m.enabled ? <Tag>{__( 'Off', 'shopglut' )}</Tag> : null}
              </div>
              <p className="sg-card-text">{m.description}</p>
              <div className="sg-card-foot"><span>{m.enabled ? __( 'Configure', 'shopglut' ) : __( 'Turn on in Modules', 'shopglut' )}</span><FontAwesomeIcon icon={faArrowRight} /></div>
            </a>
          )) : null}
        </div>

        <div className="sg-grid sg-quick">
          <a className="sg-card sg-card-link sg-card-row" href={_pg.settings}><span className="sg-module-icon"><FontAwesomeIcon icon={faGear} /></span><div><div className="sg-card-title">{__( 'Global Settings', 'shopglut' )}</div><p className="sg-card-text">{__( 'Accent color, asset loading and custom CSS.', 'shopglut' )}</p></div></a>
          <a className="sg-card sg-card-link sg-card-row" href={_pg.tools}><span className="sg-module-icon"><FontAwesomeIcon icon={faToolbox} /></span><div><div className="sg-card-title">{__( 'Tools', 'shopglut' )}</div><p className="sg-card-text">{__( 'Export, import, system status and reset.', 'shopglut' )}</p></div></a>
        </div>
      </div>
    </div>
  );
}
