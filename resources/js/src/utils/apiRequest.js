import axios from 'axios';
const createAxiosInstance = () => {

    const instance = axios.create({
        withCredentials: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    });

    // Add CSRF token to all requests
    instance.interceptors.request.use(
        config => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                config.headers['X-CSRF-TOKEN'] = token;
            }
            return config;
        },
        error => {
            return Promise.reject(error);
        }
    );

    instance.interceptors.response.use(
        response => {
            return response;
        },
        error => {
            if (error.response && error.response.status === 401) {
            }

            if (error && error.response.status == 422) {
                for (const [key, value] of Object.entries(error.response.data.errors)) {
                    error.response.data.errors[key] = typeof value == 'object' ? value.join(', ') : value;
                }
            }

            return Promise.reject(error);
        }
    );

    return instance;
};


const axiosInstance = createAxiosInstance();

const createMethod = (method) => {
    return (url, data = {}, config = {}) => {
        const requestConfig = {
            ...config,
            method,
            url,
        };

        if (['get', 'delete'].includes(method.toLowerCase())) {
            requestConfig.params = data.params ? data.params : data;
        } else {
            requestConfig.data = data;
        }

        return axiosInstance(requestConfig);
    };
};

export const axiosRequest = {
    get: createMethod('get'),
    post: createMethod('post'),
    put: createMethod('put'),
    delete: createMethod('delete'),
    patch: createMethod('patch'),
};
