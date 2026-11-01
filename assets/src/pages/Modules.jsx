import React, { useEffect, useMemo, useState } from 'react';
import { Button, Empty, Input, Segmented, Skeleton, Switch, Tag, Tooltip, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCheck, faStar, faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons';
import { __, sprintf } from '@wordpress/i18n';
import Header from '../components/Header';
import { _admin, _pg, CATEGORIES } from '../components/adminData';
import { moduleIcon, categoryLabel } from '../components/icons';
import * as api from '../services/api';
import { applyFilters } from '../extend';

function ModuleCard({ m, onToggle, busy }) {
  const locked = m.pro && !m.available;
  const soon = m.status === 'coming_soon';
  const local = _admin.modules[m.id] || {};
  const open = m.enabled && (m.layouts || m.has_settings) ? local.url : '';

  return (
    <div className={'sg-card sg-module-card' + (m.enabled ? ' is-on' : '')}>
      <div className="sg-card-head">
        <span className="sg-module-icon"><FontAwesomeIcon icon={moduleIcon(m.icon)} /></span>
        <div className="sg-card-title">
          {m.title}
          {m.pro ? <Tag color="gold" className="sg-tag-pro">Pro</Tag> : null}
          {soon ? <Tag>{__( 'Coming soon', 'shopglut' )}</Tag> : null}
          {m.status === 'beta' ? <Tag color="blue">Beta</Tag> : null}
        </div>
        <Tooltip title={locked ? __( 'Needs ShopGlut Pro', 'shopglut' ) : soon ? __( 'Not available yet', 'shopglut' ) : ''}>
          <Switch checked={m.enabled} disabled={locked || soon} loading={busy} onChange={(on) => onToggle(m, on)} />
        </Tooltip>
      </div>
      <p className="sg-card-text">{m.description}</p>
      {m.features.length ? (
        <ul className="sg-feature-list">
          {m.features.map((f) => <li key={f}><FontAwesomeIcon icon={faCheck} />{f}</li>)}
        </ul>
      ) : null}
      {m.pro_features.length && !_admin.pro_enabled ? (
        <ul className="sg-feature-list sg-feature-pro">
          {m.pro_features.map((f) => <li key={f}><FontAwesomeIcon icon={faStar} />{f}</li>)}
        </ul>
      ) : null}
      <div className="sg-card-actions">
        {open ? <Button type="primary" href={open}>{m.layouts ? sprintf( __( 'Open (%d)', 'shopglut' ), m.layout_count ) : __( 'Settings', 'shopglut' )}</Button> : null}
        {open && m.layouts && m.has_settings ? <Button href={local.settings_url}>{__( 'Settings', 'shopglut' )}</Button> : null}
        {locked && _pg.pro ? <Button href={_pg.pro} icon={<FontAwesomeIcon icon={faStar} />}>{__( 'Get Pro', 'shopglut' )}</Button> : null}
      </div>
    </div>
  );
}

export default function Modules() {
  const [modules, setModules] = useState(null);
  const [busy, setBusy] = useState('');
  const [search, setSearch] = useState('');
  const [show, setShow] = useState('all');

  useEffect(() => {
    api.getModules().then((d) => setModules(d.modules)).catch((e) => message.error(e.message));
  }, []);

  const onToggle = async (m, on) => {
    setBusy(m.id);
    try {
      const { module } = await api.toggleModule(m.id, on);
      setModules((list) => list.map((x) => (x.id === m.id ? module : x)));
      if (_admin.modules[m.id]) _admin.modules[m.id].enabled = module.enabled;
      message.success(on ? sprintf( __( '%s is on. Its page is in the menu after the next page load.', 'shopglut' ), m.title ) : sprintf( __( '%s is off.', 'shopglut' ), m.title ));
    } catch (e) {
      message.error(e.message);
    }
    setBusy('');
  };

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return (modules || []).filter((m) => (show === 'all' || (show === 'on' ? m.enabled : !m.enabled))
      && (!q || (m.title + ' ' + m.description).toLowerCase().includes(q)));
  }, [modules, search, show]);

  const categories = applyFilters('shopglut.modules.categories', CATEGORIES);

  return (
    <div className="sg-page">
      <Header />
      <div className="sg-body">
        <div className="sg-page-head">
          <div>
            <h1 className="sg-title">{__( 'Modules', 'shopglut' )}</h1>
            <p className="sg-subtitle">{__( 'Turn on only what your store uses. Modules that are off load no code on your site.', 'shopglut' )}</p>
          </div>
          <div className="sg-page-actions">
            <Segmented value={show} onChange={setShow} options={[
              { label: __( 'All', 'shopglut' ), value: 'all' },
              { label: __( 'On', 'shopglut' ), value: 'on' },
              { label: __( 'Off', 'shopglut' ), value: 'off' },
            ]} />
            <Input allowClear prefix={<FontAwesomeIcon icon={faMagnifyingGlass} />} placeholder={__( 'Search modules', 'shopglut' )} value={search} onChange={(e) => setSearch(e.target.value)} style={{ width: 240 }} />
          </div>
        </div>

        {!modules ? (
          <div className="sg-grid">{[0, 1, 2, 3, 4, 5].map((i) => <div key={i} className="sg-card"><Skeleton active /></div>)}</div>
        ) : !visible.length ? (
          <Empty description={__( 'No modules match.', 'shopglut' )} />
        ) : categories.map((cat) => {
          const items = visible.filter((m) => m.category === cat);
          if (!items.length) return null;
          return (
            <section key={cat}>
              <h2 className="sg-section-title">{categoryLabel(cat)}</h2>
              <div className="sg-grid">
                {items.map((m) => <ModuleCard key={m.id} m={m} onToggle={onToggle} busy={busy === m.id} />)}
              </div>
            </section>
          );
        })}
      </div>
    </div>
  );
}
