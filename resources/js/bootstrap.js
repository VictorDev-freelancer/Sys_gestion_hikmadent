import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo / Reverb — WebSockets
 *
 * Desactivado temporalmente. Para activar Reverb:
 * 1. Asegúrate que las variables VITE_REVERB_* en .env tengan valores correctos
 * 2. Ejecuta `php artisan reverb:start` en el servidor
 * 3. Descomenta la línea de abajo
 * 4. Ejecuta `npm run build` para recompilar
 */

// import './echo';
