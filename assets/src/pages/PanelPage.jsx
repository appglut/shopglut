import React, { useState } from 'react';
import { Button, Input, Switch, Tag, Tooltip, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft, faEye } from '@fortawesome/free-solid-svg-icons';
import { __ } from '@wordpress/i18n';
import Header from '../components/Header';
import ModuleHeader from '../components/ModuleHeader';
import { _admin, _modules } from '../components/adminData';
import * as api from '../services/api';
import { applyFilters } from '../extend';

/**
 * Header of the layout editor: back to the list, name (rename in place), template, active switch
 * and preview. The options themselves are the AppGlut panel PHP prints under this header.
 */
function LayoutBar({ layout }) {
  const m = _modules[layout.module || _admin.module] || {};
  const [name, setName] = useState(layout.name);
  const [active, setActive] = useState(layout.status === 'active');
  const [busy, setBusy] = useState(false);

  const save = async (changes) => {
    setBusy(true);
    try {
      await api.updateLayout(layout.id, changes);
      message.success(__( 'Saved.', 'shopglut' ));
      return true;
    } catch (e) {
      message.error(e.message);
      return false;
    } finally {
      setBusy(false);
    }
  };

  const rename = async () => {
    const next = name.trim();
    if (!next || next === layout.name) return setName(layout.name);
    if (await save({ name: next })) {
      layout.name = next;
      const title = document.querySelector('.sg-panel-wrap .agf-brand-title');
      if (title && title.firstChild) title.firstChild.nodeValue = next + ' ';
    }
  };

  const toggle = async (on) => {
    setActive(on);
    if (!(await save({ status: on ? 'active' : 'draft' }))) setActive(!on);
  };

  return (
    <div className="sg-layout-bar">
      <a href={m.url} className="sg-back-link"><FontAwesomeIcon icon={faArrowLeft} /> {m.title}</a>
      <Input className="sg-layout-name" value={name} onChange={(e) => setName(e.target.value)} onBlur={rename} onPressEnter={(e) => e.target.blur()} aria-label={__( 'Layout name', 'shopglut' )} />
      <Tag>{layout.template_title}</Tag>
      <div className="sg-layout-bar-right">
        {applyFilters('shopglut.layoutEditor.actions', [], { layout })}
        {layout.preview_url ? <Button href={layout.preview_url} target="_blank" icon={<FontAwesomeIcon icon={faEye} />}>{__( 'Preview', 'shopglut' )}</Button> : null}
        <Tooltip title={__( 'Active layouts are used on your store.', 'shopglut' )}>
          <label className="sg-active-toggle">
            <Switch checked={active} loading={busy} onChange={toggle} />
            <span>{active ? __( 'Active', 'shopglut' ) : __( 'Draft', 'shopglut' )}</span>
          </label>
        </Tooltip>
      </div>
    </div>
  );
}

/** Header above an AppGlut panel: Global Settings, a module's Settings, or the layout editor. */
export default function PanelPage() {
  const page = _admin.page;
  let sub = null;
  if (page === 'shopglut-layout' && _admin.layout) {
    sub = <LayoutBar layout={{ ..._admin.layout, module: _admin.module }} />;
  } else if (_admin.module) {
    sub = <div className="sg-body sg-body-tight"><ModuleHeader module={_admin.module} /></div>;
  }
  return (
    <>
      <Header />
      {sub}
    </>
  );
}
