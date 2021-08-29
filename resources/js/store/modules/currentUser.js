const state = {
    user: null,
}

const getters = {
    getUser: state => state.user,
    getUserEmail: (state, getters) => getters.getUser.userName,
    getRoles: (state, getters) => getters.getUser.roles
}

const actions = {
    login({}, token) {
        const config = {
            headers: { Authorization: `Bearer ${token}` }
        };
        axios.post('/api/v1/login', {}, config)
          .then(res => {
              //console.log(res)
          })
          .catch(err => {
              //console.error(err)
          })
    },
    logout({}) {
        axios.post('api/v1/logout')
          .then(res => {
              //console.log(res)
          })
          .catch(err => {
              //console.error(err)
          })
    }
}

const mutations = {
    SET_USER(state, user) {
        state.user = user;
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
