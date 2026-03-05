import axios from 'axios';

globalThis.axios = axios;

globalThis.axios.defaults.baseURL = window.location.origin;
globalThis.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
globalThis.axios.defaults.withCredentials = true;
globalThis.axios.defaults.withXSRFToken = true;
