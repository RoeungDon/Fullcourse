import Swal from "sweetalert2";

export function LoadingModal(title = "Please wait...") {
    return Swal.fire({
        title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

export function CloseModal() {
    return Swal.close();
}

export function MessageModal(options = {}, onConfirm) {
    return Swal.fire({
        icon: options.icon || "info",
        title: options.title || "",
        text: options.text || "",
    }).then((result) => {
        if (typeof onConfirm === "function" && result.isConfirmed) {
            onConfirm(result);
        }
        return result;
    });
}
