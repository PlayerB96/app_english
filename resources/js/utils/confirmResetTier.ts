import Swal from "sweetalert2";

export async function confirmResetTier(tierName: string): Promise<boolean> {
    const isDark = document.documentElement.classList.contains("dark");

    const result = await Swal.fire({
        title: "¿Reiniciar módulo?",
        html: `
            <p class="text-sm leading-relaxed">
                Se borrará todo el avance del módulo <strong>${tierName}</strong>
                (sus 5 subniveles): preguntas respondidas, bloqueos y progreso guardado.
            </p>
            <p class="mt-2 text-sm leading-relaxed">
                Esta acción no se puede deshacer.
            </p>
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, reiniciar módulo",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc2626",
        cancelButtonColor: isDark ? "#374151" : "#6b7280",
        reverseButtons: true,
        focusCancel: true,
        background: isDark ? "#111827" : "#ffffff",
        color: isDark ? "#f3f4f6" : "#111827",
    });

    return result.isConfirmed;
}
