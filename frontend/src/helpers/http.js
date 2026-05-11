import axios from 'axios'

const http = () => {
    const options = {
        baseURL: 'http://127.0.0.1:8000',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    }

    if (localStorage.getItem('token')) {
        options.headers.Authorization = `Bearer ${localStorage.getItem('token')}`
    }

    return axios.create(options)
}

export default http
