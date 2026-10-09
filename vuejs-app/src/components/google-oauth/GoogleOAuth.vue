<template></template>
<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { LoadingModal, MessageModal, CloseModal } from "@/functions/swal";
import { apiGoogleOAuthExchangeToken } from "@/functions/api/google-oauth";
import { useUserStore } from "@/stores/user";

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

function goSignIn() {
    CloseModal();
    router.replace({ name: "auth.signin" });
}

onMounted(async () => {
    try {
        LoadingModal("Processing Google authentication...");

        const error = route.query.error;
        if (error === "google_oauth_failed") {
            const detail = route.query.detail
                ? decodeURIComponent(String(route.query.detail))
                : "Google authentication failed. Please try again.";
            return MessageModal(
                {
                    icon: "error",
                    title: "Error",
                    text: detail,
                },
                goSignIn,
            );
        }

        const token = route.query.token;
        if (!token) {
            return MessageModal(
                {
                    icon: "error",
                    title: "Error",
                    text: "Missing Google auth token. Please try again.",
                },
                goSignIn,
            );
        }

        const response = await apiGoogleOAuthExchangeToken(token);
        const { data } = response;
        userStore.setState(data.user);
        userStore.setSanctumToken(data.token);
        CloseModal();
        return router.replace({ name: "dashboard" });
    } catch (e) {
        const msg =
            e?.response?.data?.message ||
            e?.message ||
            "Google authentication failed. Please try again.";
        return MessageModal(
            {
                icon: "error",
                title: "Error",
                text: msg,
            },
            goSignIn,
        );
    }
});
</script>
