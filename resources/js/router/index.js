import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import admision from '../components/admision/container'
import controlador from '../components/controlador/container'
import pcr from '../components/pcr/container'
import supervisor from '../components/supervisor/container'
import supervisor_pcr from '../components/supervisor_pcr/container'
import estadisticas from '../components/estadisticas/container'
import estadisticas_rend from '../components/estadisticas_rend/container'
import salud from '../components/salud/container'
import whatsapp from '../components/whatsapp/container'
import perfil_paciente from "../components/salud/perfil_paciente";
import NuevaFicha from "../components/admision/NuevaFicha";
import TablaAdmision from "../components/admision/TablaAdmision";
import admin_pcr from "../components/admin_pcr/container"
import pacientes from "../components/mantenimientos/pacientes/container";
import TablaPacientes from "../components/mantenimientos/pacientes/TablaPacientes"
import EditarPaciente from "../components/mantenimientos/pacientes/EditarPaciente";
//import Menu from "../components/home/Menu";
import NuevoPaciente from "../components/admision/NuevoPaciente";
import ResponceCenter from "../components/responce_center/container";
import EditarFe from "../components/responce_center/EditarFe";
import TablaRC from "../components/responce_center/TablaRC";
import EditarFc from "../components/responce_center/EditarFc";
import envio_pcr from "../components/envio_pcr/container"
import inteligenciaSanitaria from "../components/inteligencia_sanitaria/container"
import PruebasAntigenas from "../components/pruebas_antigenas/container"
import EditarIm from "../components/responce_center/EditarIm";
import store from '../store'
import Firma from "../components/admision/Firma";


const routes = [
    {
        path: '/',
        name: 'inicio',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && roles.includes('SupervisorPcrPrs')) {
                    next();
                } else if (roles && (roles.includes('Salud') || roles.includes('CerroVerde'))) {
                    next({name: 'salud'});
                } else if(roles && roles.includes('SupervisorPcr')) {
                    next({name: 'supervisor_pcr'});
                } else if(roles && roles.includes('AdminPcr')) {
                    next({name: 'admin_pcr'});
                } else if(roles && roles.includes('ResponceCenter')) {
                    next({name: 'TablaRC'});
                } else {
                    next();
                }
            }, 100)
        }
    },
    {
        component: admision,
        path: '/admision',
        children: [
            { path: '', component: TablaAdmision, name: 'tabla_admision'},
            { path: 'nueva_ficha', component: NuevaFicha, name: 'nueva_ficha'},
            { path: 'nuevo_paciente', component: NuevoPaciente, name: 'nuevo_paciente'},
            { path: 'firma/:numero_documento', component: Firma, name: 'firma'}
        ],
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && roles.includes('SupervisorPcrPrs')) {
                    next();
                } else if(roles && (roles.includes('Salud') || roles.includes('CerroVerde') || roles.includes('AdminPcr')
                  || roles.includes('SupervisorPcr') || roles.includes('ResponceCenter'))) {
                    next({name: 'inicio'});
                } else {
                    next();
                }
            }, 100)
        }
    },
    {
        component: controlador,
        name: 'controlador',
        path: '/controlador',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && roles.includes('SupervisorPcrPrs')) {
                    next();
                } else if(roles && (roles.includes('Salud') || roles.includes('CerroVerde') || roles.includes('AdminPcr')
                  || roles.includes('SupervisorPcr') || roles.includes('ResponceCenter'))) {
                    next({name: 'inicio'});
                } else {
                    next();
                }
            }, 100)
        }
    },
    {
        component: pcr,
        name: 'pcr',
        path: '/pcr',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && roles.includes('SupervisorPcrPrs')) {
                    next();
                } else if(roles && (roles.includes('Salud') || roles.includes('CerroVerde') || roles.includes('AdminPcr')
                  || roles.includes('SupervisorPcr') || roles.includes('ResponceCenter'))) {
                    next({name: 'inicio'});
                } else {
                    next();
                }
            }, 100)
        }
    },
    {
        component: supervisor,
        name: 'supervisor',
        path: '/supervisor',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && roles.includes('SupervisorPcrPrs')) {
                    next();
                } else if(roles && (roles.includes('Salud') || roles.includes('CerroVerde') || roles.includes('AdminPcr')
                  || roles.includes('SupervisorPcr') || roles.includes('ResponceCenter'))) {
                    next({name: 'inicio'});
                } else {
                    next();
                }
            }, 100)
        }
    },
    {
        component: supervisor_pcr,
        name: 'supervisor_pcr',
        path: '/supervisor_pcr',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('AdminPcr')  ||
                  roles.includes('SupervisorPcr') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: admin_pcr,
        name: 'admin_pcr',
        path: '/administrador_pcr',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('AdminPcr') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: whatsapp,
        name: 'whatsapp',
        path: '/whatsapp',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: estadisticas,
        name: 'estadisticas',
        path: '/estadisticas',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: estadisticas_rend,
        name: 'estadisticas_rend',
        path: '/estadisticas_rend',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: salud,
        name: 'salud',
        path: "/salud",
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('CerroVerde') || roles.includes('Salud') || roles.includes('Admin')
                  || roles.includes('ResponceCenter'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: perfil_paciente,
        name: "perfil_paciente",
        path: "/salud/:id_paciente",
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('CerroVerde') || roles.includes('Salud') || roles.includes('Admin')
                  || roles.includes('ResponceCenter'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: ResponceCenter,
        path: '/rc',
        children: [
            { path: '', component: TablaRC, name: 'TablaRC' },
            {
                path: 'fe',
                component: EditarFe,
                name: 'EditarFe',
                beforeEnter (to, from, next) {
                    if(store.state.rc.id_ev) next();
                    else next('/rc');
                }
            },
            {
                path: 'fc',
                component: EditarFc,
                name: 'EditarFc',
                beforeEnter (to, from, next) {
                    if(store.state.rc.id_ev) next();
                    else next('/rc');
                }
            },
            {
                path: 'im/:idEvidencia',
                component: EditarIm,
                name: 'EditarIm',
            }
        ],
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('ResponceCenter') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    /*{
        component: envio_pcr,
        path: '/envio_pcr',
        name: 'EnvioPcr'
    },
    {
        component: pacientes,
        path: '/pacientes',
        name: 'Pacientes',
        children: [
            {path: '', component: TablaPacientes, name: 'TablaPacientes'},
            {path: 'editar/:id_pac', component: EditarPaciente, name: 'EditarPaciente'},
        ]
    }*/
    {
        component: inteligenciaSanitaria,
        path: '/is',
        name: 'InteligenciaSanitaria',
        beforeEnter(to, from ,next) {
            setTimeout(() => {
                const roles = store.state.currentUser.user.roles;
                if (roles && (roles.includes('SupervisorPcrPrs') || roles.includes('ResponceCenter') || roles.includes('Admin'))) {
                    next();
                } else {
                    next({name: 'inicio'});
                }
            }, 100)
        }
    },
    {
        component: PruebasAntigenas,
        path: '/pruebas_antigenas',
        name: 'PruebasAntigenas'
    },
];

const router = new VueRouter({
    routes,
})

/*router.beforeEach((to, from ,next) => {
    if (store.state.currentUser.user) {
        next()
    } else {
        next(false)
    }
})*/

export default router
