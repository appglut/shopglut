/**
 * Shared admin components and helpers every page offers to add-ons (window.shopglut.components / utils).
 */
import Header from '../components/Header';
import ModuleHeader from '../components/ModuleHeader';
import { moduleIcon } from '../components/icons';
import * as api from '../services/api';
import { expose } from './index';

expose({ Header, ModuleHeader }, { api, moduleIcon });
