import React, { useCallback, useEffect, useState } from 'react';
import { Button, Dropdown, Empty, Input, InputNumber, Modal, Popconfirm, Segmented, Switch, Table, Tag, Tooltip, message } from 'antd';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlus, faPen, faClone, faTrash, faLock, faMagnifyingGlass, faStar, faImage } from '@fortawesome/free-solid-svg-icons';
import { __, sprintf } from '@wordpress/i18n';
import Header from '../components/Header';
import ModuleHeader from '../components/ModuleHeader';
import { _admin, _modules, _pg } from '../components/adminData';
import * as api from '../services/api';
import { applyFilters } from '../extend';

/** Template picker: free templates, then Pro ones (locked without ShopGlut Pro). */
function TemplatePicker({ open, templates, label, onClose, onCreate }) {
  const [name, setName] = useState('');
  const [picked, setPicked] = useState('');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (open) {
      setName('');
      const first = templates.find((t) => !t.locked);
      setPicked(first ? first.id : '');
    }
  }, [open, templates]);

  const create = async () => {
    setBusy(true);
    try {
      await onCreate(picked, name);
    } finally {
      setBusy(false);
    }
  };

  const single = templates.length <= 1;

  return (
    <Modal
      open={open}
      onCancel={onClose}
      width={single ? 480 : 960}
      title={sprintf( __( 'New %s', 'shopglut' ), label.toLowerCase() )}
      okText={__( 'Create & edit', 'shopglut' )}
      onOk={create}
      okButtonProps={{ disabled: !picked, loading: busy }}
      rootClassName="sg-modal"
    >
      <Input
        size="large"
        placeholder={sprintf( __( '%s name (optional)', 'shopglut' ), label )}
        value={name}
        onChange={(e) => setName(e.target.value)}
        onPressEnter={create}
        style={{ marginBottom: single ? 0 : 18 }}
      />
      {!single ? (
        <div className="sg-template-grid">
          {templates.map((t) => (
            <button
              type="button"
              key={t.id}
              className={'sg-template' + (picked === t.id ? ' is-picked' : '') + (t.locked ? ' is-locked' : '')}
              onClick={() => !t.locked && setPicked(t.id)}
              aria-pressed={picked === t.id}
              disabled={t.locked}
            >
              <span className="sg-template-thumb">
                {t.image ? <img src={t.image} alt="" loading="lazy" /> : <span className="sg-template-ph"><FontAwesomeIcon icon={faImage} /><span>{t.title}</span></span>}
                {t.pro ? <span className="sg-template-badge">{t.locked ? <FontAwesomeIcon icon={faLock} /> : null} Pro</span> : null}
              </span>
              <span className="sg-template-name">{t.title}</span>
            </button>
          ))}
        </div>
      ) : null}
      {!single && templates.some((t) => t.locked) && _pg.pro ? (
        <div className="sg-pro-note">
          <FontAwesomeIcon icon={faStar} /> {__( 'Pro templates unlock with ShopGlut Pro.', 'shopglut' )} <a href={_pg.pro}>{__( 'See what Pro adds', 'shopglut' )}</a>
        </div>
      ) : null}
    </Modal>
  );
}

export default function Layouts() {
  const module = _admin.module;
  const info = _modules[module] || { title: module, layout_label: __( 'Layout', 'shopglut' ) };
  const label = info.layout_label || __( 'Layout', 'shopglut' );

  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState('');
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState([]);
  const [picker, setPicker] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      setData(await api.getLayouts(module, applyFilters('shopglut.layouts.params', { status, search, page, per_page: 20 }, { module })));
    } catch (e) {
      message.error(e.message);
    }
    setLoading(false);
  }, [module, status, search, page]);

  useEffect(() => { load(); }, [load]);

  const patch = async (row, changes) => {
    try {
      const { layout } = await api.updateLayout(row.id, changes);
      setData((d) => ({ ...d, items: d.items.map((x) => (x.id === row.id ? layout : x)) }));
      if (changes.status) load();
    } catch (e) {
      message.error(e.message);
    }
  };

  const remove = async (row) => {
    try {
      await api.deleteLayout(row.id);
      message.success(__( 'Deleted.', 'shopglut' ));
      load();
    } catch (e) {
      message.error(e.message);
    }
  };

  const duplicate = async (row) => {
    try {
      await api.duplicateLayout(row.id);
      message.success(__( 'Copied as a draft.', 'shopglut' ));
      load();
    } catch (e) {
      message.error(e.message);
    }
  };

  const bulk = async (action) => {
    try {
      const { done } = await api.bulkLayouts(module, selected, action);
      message.success(sprintf( __( '%d updated.', 'shopglut' ), done ));
      setSelected([]);
      load();
    } catch (e) {
      message.error(e.message);
    }
  };

  const create = async (template, name) => {
    try {
      const { edit_url } = await api.createLayout(module, template, name);
      window.location.href = edit_url;
    } catch (e) {
      message.error(e.message);
    }
  };

  const templates = data ? data.templates : [];
  const showTemplate = templates.length > 1;

  const columns = applyFilters('shopglut.layouts.columns', [
    {
      title: __( 'Name', 'shopglut' ),
      dataIndex: 'name',
      render: (v, row) => (
        <div className="sg-cell-name">
          <a href={row.edit_url} className="sg-row-title">{v || __( '(no name)', 'shopglut' )}</a>
          {row.summary ? <div className="sg-row-meta">{row.summary}</div> : null}
          <div className="sg-row-actions">
            <a href={row.edit_url}>{__( 'Edit', 'shopglut' )}</a>
            <a onClick={() => duplicate(row)}>{__( 'Duplicate', 'shopglut' )}</a>
            <Popconfirm title={__( 'Delete this layout?', 'shopglut' )} okText={__( 'Delete', 'shopglut' )} okButtonProps={{ danger: true }} onConfirm={() => remove(row)}>
              <a className="sg-action-delete">{__( 'Delete', 'shopglut' )}</a>
            </Popconfirm>
          </div>
        </div>
      ),
    },
    ...(showTemplate ? [{
      title: __( 'Template', 'shopglut' ),
      dataIndex: 'template_title',
      width: 200,
      render: (v, row) => <span>{v}{row.locked ? <Tooltip title={__( 'Needs ShopGlut Pro', 'shopglut' )}><Tag color="gold" style={{ marginLeft: 6 }}>Pro</Tag></Tooltip> : null}</span>,
    }] : []),
    {
      title: <Tooltip title={__( 'When several active layouts match, the highest priority wins.', 'shopglut' )}>{__( 'Priority', 'shopglut' )}</Tooltip>,
      dataIndex: 'priority',
      width: 110,
      render: (v, row) => <InputNumber size="small" min={0} max={999} defaultValue={v} style={{ width: 70 }} onBlur={(e) => Number(e.target.value) !== v && patch(row, { priority: Number(e.target.value) })} />,
    },
    {
      title: __( 'Active', 'shopglut' ),
      dataIndex: 'status',
      width: 90,
      render: (v, row) => <Switch size="small" checked={v === 'active'} disabled={row.locked && v !== 'active'} onChange={(on) => patch(row, { status: on ? 'active' : 'draft' })} />,
    },
    {
      title: __( 'Updated', 'shopglut' ),
      dataIndex: 'updated_at',
      width: 170,
      render: (v) => <span className="sg-muted">{new Date(v).toLocaleString()}</span>,
    },
    {
      title: '',
      key: 'actions',
      width: 120,
      align: 'right',
      render: (_, row) => (
        <span className="sg-icon-actions">
          <Tooltip title={__( 'Edit', 'shopglut' )}><Button type="text" href={row.edit_url} icon={<FontAwesomeIcon icon={faPen} />} /></Tooltip>
          <Tooltip title={__( 'Duplicate', 'shopglut' )}><Button type="text" onClick={() => duplicate(row)} icon={<FontAwesomeIcon icon={faClone} />} /></Tooltip>
          <Popconfirm title={__( 'Delete this layout?', 'shopglut' )} okText={__( 'Delete', 'shopglut' )} okButtonProps={{ danger: true }} onConfirm={() => remove(row)}>
            <Button type="text" danger icon={<FontAwesomeIcon icon={faTrash} />} />
          </Popconfirm>
        </span>
      ),
    },
  ], { module, reload: load });

  const counts = data ? data.counts : { all: 0, active: 0, draft: 0 };
  const newButton = (
    <Button type="primary" icon={<FontAwesomeIcon icon={faPlus} />} onClick={() => setPicker(true)} disabled={!data}>
      {sprintf( __( 'New %s', 'shopglut' ), label.toLowerCase() )}
    </Button>
  );

  return (
    <div className="sg-page">
      <Header />
      <div className="sg-body">
        <ModuleHeader module={module} description={data ? data.module.description : ''} actions={newButton} />

        <div className="sg-table-card">
          <div className="sg-toolbar">
            <Segmented value={status} onChange={(v) => { setStatus(v); setPage(1); }} options={[
              { label: sprintf( __( 'All (%d)', 'shopglut' ), counts.all ), value: '' },
              { label: sprintf( __( 'Active (%d)', 'shopglut' ), counts.active ), value: 'active' },
              { label: sprintf( __( 'Draft (%d)', 'shopglut' ), counts.draft ), value: 'draft' },
            ]} />
            <div className="sg-toolbar-right">
              {selected.length ? (
                <Dropdown menu={{
                  items: [
                    { key: 'active', label: __( 'Activate', 'shopglut' ) },
                    { key: 'draft', label: __( 'Deactivate', 'shopglut' ) },
                    { type: 'divider' },
                    { key: 'delete', label: __( 'Delete', 'shopglut' ), danger: true },
                  ],
                  onClick: ({ key }) => (key === 'delete' ? Modal.confirm({ title: sprintf( __( 'Delete %d layouts?', 'shopglut' ), selected.length ), okButtonProps: { danger: true }, onOk: () => bulk('delete') }) : bulk(key)),
                }}>
                  <Button>{sprintf( __( 'Bulk actions (%d)', 'shopglut' ), selected.length )}</Button>
                </Dropdown>
              ) : null}
              <Input allowClear placeholder={__( 'Search by name', 'shopglut' )} prefix={<FontAwesomeIcon icon={faMagnifyingGlass} />} onPressEnter={(e) => { setSearch(e.target.value); setPage(1); }} onChange={(e) => { if (!e.target.value) { setSearch(''); setPage(1); } }} style={{ width: 240 }} />
            </div>
          </div>

          <Table
            rowKey="id"
            loading={loading}
            columns={columns}
            dataSource={data ? data.items : []}
            rowSelection={{ selectedRowKeys: selected, onChange: setSelected }}
            pagination={{ current: page, pageSize: 20, total: data ? data.total : 0, onChange: setPage, hideOnSinglePage: true, showSizeChanger: false }}
            locale={{
              emptyText: (
                <Empty description={search || status ? __( 'Nothing matches.', 'shopglut' ) : sprintf( __( 'No %s yet. Create one from a template.', 'shopglut' ), (info.layouts_label || __( 'Layouts', 'shopglut' )).toLowerCase() )}>
                  {!search && !status ? newButton : null}
                </Empty>
              ),
            }}
          />
        </div>
      </div>

      <TemplatePicker open={picker} templates={templates} label={label} onClose={() => setPicker(false)} onCreate={create} />
    </div>
  );
}
