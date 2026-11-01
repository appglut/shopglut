import React from 'react';
import { ConfigProvider } from 'antd';
import { applyFilters } from '../extend';

/** antd theme of every ShopGlut page (add-ons can adjust it with the shopglut.theme filter). */
export default function Root({ children }) {
  const theme = applyFilters('shopglut.theme', {
    token: { colorPrimary: '#7c3aed', borderRadius: 8, fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif' },
  });
  return <ConfigProvider theme={theme}>{children}</ConfigProvider>;
}
