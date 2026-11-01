import { boot } from '../extend';
import '../extend/common';
import React from 'react';
import { createRoot } from 'react-dom/client';
import '../admin.css';
import Root from '../components/Root';
import Dashboard from './Dashboard';

boot(() => createRoot(document.getElementById('shopglut-root')).render(<Root><Dashboard /></Root>));
