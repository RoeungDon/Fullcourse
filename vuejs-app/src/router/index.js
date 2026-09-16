import Signin from "@/components/auth/Signin.vue";
import Signout from "@/components/auth/Signout.vue";
import Signup from "@/components/auth/Signup.vue";
import VerifyEmail from "@/components/auth/VerifyEmail.vue";
import Dashboard from "@/components/pages/Dashboard.vue";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: "/",
            redirect: { name: "auth.signin" },
        },
        {
            path: "/signin",
            name: "auth.signin",
            component: Signin,
        },
        {
            path: "/signout",
            name: "auth.signout",
            component: Signout,
        },
        {
            path: "/signup",
            name: "auth.signup",
            component: Signup,
        },
        {
            path: "/verify/email",
            name: "auth.verify.email",
            component: VerifyEmail,
        },
        {
            path: "/dashboard",
            name: "dashboard",
            component: Dashboard,
        },
        {
            path: "/:pathMatch(.*)*",
            redirect: { name: "dashboard" },
        },
    ],
});

export default router;