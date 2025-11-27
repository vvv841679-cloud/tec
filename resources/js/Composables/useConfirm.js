import {router} from '@inertiajs/vue3'
import Swal from "sweetalert2";

export function useConfirm() {
    return (url, options = {}) => {
        Swal.fire({
            title: options.title || 'Estas Seguro?',
            text: options.text || "¡Esta acción no se puede deshacer!",
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: options.confirmButtonColor || '#d33',
            cancelButtonColor: options.cancelButtonColor || '#3085d6',
            confirmButtonText: options.confirmButtonText || 'Si Eliminar!',
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(url)
            }
        })
    }
}
