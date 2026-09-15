import axios from 'axios'

const API_URL = import.meta.env.VITE_APP_API_URL

/**
 * POST /api/signup
 * Body: { name, email, password }
 * 201 → { message, user }
 */
export async function signup(payload) {
    return axios.post(`${API_URL}/signup`, payload)
}

/**
 * POST /api/signin
 * Body: { email, password }
 * 200 → { message, user, token }
 */
export async function signin(payload) {
    return axios.post(`${API_URL}/signin`, payload)
}

/**
 * POST /api/signout  (auth:sanctum)
 * Header: Authorization: Bearer <token>
 * 200 → { message }
 */
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

/**
 * GET /api/verify  (auth:sanctum)
 * Header: Authorization: Bearer <token>
 * 200 → { message, user }
 */
export async function verify(token) {
    return axios.get(`${API_URL}/verify`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    })
}