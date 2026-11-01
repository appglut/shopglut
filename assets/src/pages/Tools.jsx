import React, { useEffect, useState } from 'react';
import { Button, Checkbox, Popconfirm, Skeleton, Tabs, Upload, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faDownload, faUpload, faTriangleExclamation, faCircleCheck, faCircleXmark, faCopy } from '@fortawesome/free-solid-svg-icons';
import { __, sprintf } from '@wordpress/i18n';
import Header from '../components/Header';
import * as api from '../services/api';
import { applyFilters } from '../extend';

function ExportImport() {
  const [what, setWhat] = useState(['settings', 'layouts']);
  const [busy, setBusy] = useState(false);

  const download = async () => {
    setBusy(true);
    try {
      const { filename, data } = await api.exportData(what);
      const url = URL.createObjectURL(new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' }));
      const a = Object.assign(document.createElement('a'), { href: url, download: filename });
      a.click();
      URL.revokeObjectURL(url);
    } catch (e) {
      message.error(e.message);
    }
    setBusy(false);
  };

  const upload = async (file) => {
    try {
      const res = await api.importData(await file.text());
      message.success(sprintf( __( 'Imported %1$d settings groups and %2$d layouts (as drafts).', 'shopglut' ), res.settings, res.layouts ));
    } catch (e) {
      message.error(e.message);
    }
    return false;
  };

  return (
    <div className="sg-grid sg-grid-2">
      <div className="sg-card">
        <div className="sg-card-title">{__( 'Export', 'shopglut' )}</div>
        <p className="sg-card-text">{__( 'Download a JSON file to move your setup to another site or keep a backup.', 'shopglut' )}</p>
        <Checkbox.Group value={what} onChange={setWhat} options={[
          { label: __( 'Settings (modules, global and module settings)', 'shopglut' ), value: 'settings' },
          { label: __( 'Layouts of every builder', 'shopglut' ), value: 'layouts' },
        ]} className="sg-stack" />
        <Button type="primary" icon={<FontAwesomeIcon icon={faDownload} />} loading={busy} disabled={!what.length} onClick={download}>{__( 'Download export', 'shopglut' )}</Button>
      </div>
      <div className="sg-card">
        <div className="sg-card-title">{__( 'Import', 'shopglut' )}</div>
        <p className="sg-card-text">{__( 'Settings in the file replace the current ones. Layouts are added as drafts, so nothing changes on the store until you activate them.', 'shopglut' )}</p>
        <Upload.Dragger accept=".json,application/json" showUploadList={false} beforeUpload={upload}>
          <p className="sg-upload-icon"><FontAwesomeIcon icon={faUpload} /></p>
          <p>{__( 'Drop a ShopGlut export here, or click to choose it', 'shopglut' )}</p>
        </Upload.Dragger>
      </div>
    </div>
  );
}

function Status() {
  const [rows, setRows] = useState(null);
  useEffect(() => {
    api.getSystemStatus().then((d) => setRows(d.rows)).catch((e) => message.error(e.message));
  }, []);
  if (!rows) return <Skeleton active />;

  const copy = () => {
    navigator.clipboard.writeText(rows.map((r) => `${r.label}: ${r.value}`).join('\n')).then(() => message.success(__( 'Copied.', 'shopglut' )));
  };

  return (
    <div className="sg-card">
      <div className="sg-card-head">
        <div className="sg-card-title">{__( 'System status', 'shopglut' )}</div>
        <Button icon={<FontAwesomeIcon icon={faCopy} />} onClick={copy}>{__( 'Copy for support', 'shopglut' )}</Button>
      </div>
      <table className="sg-status-table">
        <tbody>
          {rows.map((r) => (
            <tr key={r.label}>
              <th>{r.label}</th>
              <td>
                {r.ok === true ? <FontAwesomeIcon icon={faCircleCheck} className="sg-ok" /> : r.ok === false ? <FontAwesomeIcon icon={faCircleXmark} className="sg-bad" /> : null}
                {r.value}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function Reset() {
  const run = async (what) => {
    try {
      await api.resetData(what);
      message.success(__( 'Done.', 'shopglut' ));
    } catch (e) {
      message.error(e.message);
    }
  };
  return (
    <div className="sg-grid sg-grid-2">
      <div className="sg-card sg-card-danger">
        <div className="sg-card-title"><FontAwesomeIcon icon={faTriangleExclamation} /> {__( 'Reset settings', 'shopglut' )}</div>
        <p className="sg-card-text">{__( 'Module on/off states, Global Settings and every module\'s Settings go back to their defaults. Layouts stay.', 'shopglut' )}</p>
        <Popconfirm title={__( 'Reset all ShopGlut settings?', 'shopglut' )} okButtonProps={{ danger: true }} onConfirm={() => run('settings')}>
          <Button danger>{__( 'Reset settings', 'shopglut' )}</Button>
        </Popconfirm>
      </div>
      <div className="sg-card sg-card-danger">
        <div className="sg-card-title"><FontAwesomeIcon icon={faTriangleExclamation} /> {__( 'Delete all layouts', 'shopglut' )}</div>
        <p className="sg-card-text">{__( 'Removes the layouts of every builder. Export them first if you may need them again.', 'shopglut' )}</p>
        <Popconfirm title={__( 'Delete every layout? This cannot be undone.', 'shopglut' )} okButtonProps={{ danger: true }} onConfirm={() => run('layouts')}>
          <Button danger>{__( 'Delete all layouts', 'shopglut' )}</Button>
        </Popconfirm>
      </div>
    </div>
  );
}

export default function Tools() {
  const tabs = applyFilters('shopglut.tools.tabs', [
    { key: 'transfer', label: __( 'Export / Import', 'shopglut' ), children: <ExportImport /> },
    { key: 'status', label: __( 'System Status', 'shopglut' ), children: <Status /> },
    { key: 'reset', label: __( 'Reset', 'shopglut' ), children: <Reset /> },
  ]);
  return (
    <div className="sg-page">
      <Header />
      <div className="sg-body">
        <div className="sg-page-head">
          <div>
            <h1 className="sg-title">{__( 'Tools', 'shopglut' )}</h1>
            <p className="sg-subtitle">{__( 'Back up, move and check your ShopGlut setup.', 'shopglut' )}</p>
          </div>
        </div>
        <Tabs items={tabs} defaultActiveKey={(window.location.hash || '').replace('#', '') || 'transfer'} onChange={(k) => window.history.replaceState(null, '', '#' + k)} />
      </div>
    </div>
  );
}
