import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { configureEcho } from "@laravel/echo-vue";

configureEcho({
  broadcaster: "reverb",
});
