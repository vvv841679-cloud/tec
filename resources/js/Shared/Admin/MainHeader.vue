<template>
    <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="navbar">
                <div class="container-xl">
                    <div class="row flex-column flex-md-row flex-fill align-items-center">
                        <div class="col">
                            <!-- BEGIN NAVBAR MENU -->
                            <ul class="navbar-nav">
                                <li class="nav-item" v-for="link in dynamicMenus"
                                    :key="link.id"
                                    :class="{'dropdown': link.children && link.children.length > 0,'active': checkLinkIsActive(link)}">
                                    <Link class="nav-link" :href="link.route_name !== '#' ? route(link.route_name) : '#'"
                                          v-if="!link.children || link.children.length === 0">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <Component :is="getIcon(link.icon)" class="icon"/>
                                        </span>
                                        <span class="nav-link-title">{{ link.name }}</span>
                                    </Link>

                                    <a
                                        v-if="link.children && link.children.length > 0"
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-base"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <Component :is="getIcon(link.icon)" class="icon"/>
                                        </span>
                                        <span class="nav-link-title">{{ link.name }}</span>
                                    </a>
                                    <div class="dropdown-menu" v-if="link.children && link.children.length > 0">
                                        <div class="dropdown-menu-columns">
                                            <div class="dropdown-menu-column">
                                                <Link class="dropdown-item"
                                                      v-for="item in link.children"
                                                      :key="item.id"
                                                      :href="item.route_name !== '#' ? route(item.route_name) : '#'">
                                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                        <Component v-if="item.icon" :is="getIcon(item.icon)" class="icon"/>
                                                    </span>
                                                    <span class="nav-link-title">{{ item.name }}</span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <!-- END NAVBAR MENU -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
<script setup lang="ts">
import {
    IconHome, IconUsers, IconUser,
    IconShieldLock, IconBuildingSkyscraper,
    IconWorld, IconBed, IconClipboardData, IconSofa, IconBuildingBurjAlArab, IconMeat,
    IconScale, IconUserCircle, IconCalendarWeek, IconCalendarCheck, IconBrandMastercard
} from "@tabler/icons-vue"
import {usePage} from '@inertiajs/vue3'
import {computed, h} from "vue";

const page = usePage();

// Mapa de iconos disponibles
const iconMap = {
    'IconHome': IconHome,
    'IconUsers': IconUsers,
    'IconUser': IconUser,
    'IconShieldLock': IconShieldLock,
    'IconBuildingSkyscraper': IconBuildingSkyscraper,
    'IconWorld': IconWorld,
    'IconBed': IconBed,
    'IconClipboardData': IconClipboardData,
    'IconSofa': IconSofa,
    'IconBuildingBurjAlArab': IconBuildingBurjAlArab,
    'IconMeat': IconMeat,
    'IconScale': IconScale,
    'IconUserCircle': IconUserCircle,
    'IconCalendarWeek': IconCalendarWeek,
    'IconCalendarCheck': IconCalendarCheck,
    'IconBrandMastercard': IconBrandMastercard,
};

// Obtener menús dinámicos desde las props compartidas
const dynamicMenus = computed(() => page.props.menus || []);

// Función para obtener el icono desde el mapa
const getIcon = (iconName: string) => {
    const IconComponent = iconMap[iconName];
    return IconComponent ? h(IconComponent) : null;
};

const checkLinkIsActive = (link) => {
    if (link.route_name === '#') return false;
    
    return page.url === route(link.route_name, {}, false) ||
        link.children?.some(item => item.route_name !== '#' && route(item.route_name, {}, false) === page.url)
}
</script>
