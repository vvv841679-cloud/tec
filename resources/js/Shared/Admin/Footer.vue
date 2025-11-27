<template>
    <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
                <div class="col-lg-auto ms-lg-auto">
                    <ul class="list-inline list-inline-dots mb-0">
                        <li class="list-inline-item">
                            <i class="ti ti-eye"></i> Visitas a esta página: <strong>{{ $page.props.pageViews }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <ul class="list-inline list-inline-dots mb-0">


                    </ul>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import {IconHeart} from "@tabler/icons-vue";
</script>
