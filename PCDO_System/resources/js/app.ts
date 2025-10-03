import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
// Common Imports
import { Head, Link, Form } from '@inertiajs/vue3';
// Common Components
import Button from './components/ui/button/Button.vue';
import Label from './components/ui/label/Label.vue';
import Input from './components/ui/input/Input.vue';
import InputField from './components/ui/input/Input.vue';
// Common Library Components 
import { Table, TableCaption, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { CheckCircle, XCircle, CircleDashed, Search, SquarePen } from 'lucide-vue-next';
import { DropdownMenuRoot, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from 'reka-ui';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger, } from '@/components/ui/alert-dialog'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
// Directives
import ClickOutside from './directives/ClickOutside';
//Charts
import VueApexCharts from "vue3-apexcharts";

const appName = import.meta.env.VITE_APP_NAME || 'Unknown ENV';
const VueApexChartsComponent = VueApexCharts as any;

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexChartsComponent)
            .directive('click-outside', ClickOutside)
            .component('Head', Head)
            .component('Link', Link)
            .component('Button', Button)
            .component('InputField', InputField)
            .component('Form', Form)
            .component('Table', Table)
            .component('Label', Label)
            .component('Input', Input)
            .component('TableBody', TableBody)
            .component('TableCell', TableCell)
            .component('TableHead', TableHead)
            .component('TableHeader', TableHeader)
            .component('TableRow', TableRow)
            .component('TableCaption', TableCaption)
            .component('DropdownMenuRoot', DropdownMenuRoot)
            .component('DropdownMenuTrigger', DropdownMenuTrigger)
            .component('DropdownMenuContent', DropdownMenuContent)
            .component('DropdownMenuItem', DropdownMenuItem)
            .component('CheckCircle', CheckCircle)
            .component('XCircle', XCircle)
            .component('CircleDashed', CircleDashed)
            .component('Search', Search)
            .component('SquarePen', SquarePen)
            .component('AlertDialog', AlertDialog)
            .component('AlertDialogAction', AlertDialogAction)
            .component('AlertDialogCancel', AlertDialogCancel)
            .component('AlertDialogContent', AlertDialogContent)
            .component('AlertDialogDescription', AlertDialogDescription)
            .component('AlertDialogFooter', AlertDialogFooter)
            .component('AlertDialogHeader', AlertDialogHeader)
            .component('AlertDialogTitle', AlertDialogTitle)
            .component('AlertDialogTrigger', AlertDialogTrigger)
            .component('Alert', Alert)
            .component('AlertDescription', AlertDescription)
            .component('AlertTitle', AlertTitle)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
