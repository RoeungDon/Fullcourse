import axios from 'axios'

const API_URL = import.meta.env.VITE_APP_API_URL
const APP_VERIFY_EMAIL_URL = import.meta.env.VITE_APP_VERIFY_EMAIL_URL

/**
 * POST /api/signup
 * Body includes callback_url so the verification email opens the SPA first.
 */
export async function signup(payload) {
    return axios.post(`${API_URL}/signup`, {
        ...payload,
        callback_url: APP_VERIFY_EMAIL_URL,
    })
}

export async function signin(payload) {
    return axios.post(`${API_URL}/signin`, payload)
}

export async function signout(token) {
    return axios.post(
        `${API_URL}/signout`,
        {},
        {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        },
    )
}

/** Session/token check (auth:sanctum) — NOT the email-link verifier */
export async function verify(token) {
    return axios.get(`${API_URL}/verify`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    })
}

/**
 * POST /api/send/verification-email
 * Public: { email, callback_url }
 */
export async function sendVerificationEmail(email) {
    return axios.post(`${API_URL}/send/verification-email`, {
        email,
        callback_url: APP_VERIFY_EMAIL_URL,
    })
}