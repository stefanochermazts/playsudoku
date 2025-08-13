import './bootstrap';
import Alpine from 'alpinejs';

// Evita di inizializzare Alpine più volte (Vite HMR / duplicazioni)
if (!window.Alpine) {
	window.Alpine = Alpine;
	Alpine.start();
}
