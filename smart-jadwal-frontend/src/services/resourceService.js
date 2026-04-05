import api from './api'

const API_URL = '/api'

export const kelasService = {
  getAll() {
    return api.get(`${API_URL}/kelas`)
  },

  getById(id) {
    return api.get(`${API_URL}/kelas/${id}`)
  },

  create(data) {
    return api.post(`${API_URL}/kelas`, data)
  },

  update(id, data) {
    return api.put(`${API_URL}/kelas/${id}`, data)
  },

  delete(id) {
    return api.delete(`${API_URL}/kelas/${id}`)
  }
}

export const mapelService = {
  getAll() {
    return api.get(`${API_URL}/mapel`)
  },

  getById(id) {
    return api.get(`${API_URL}/mapel/${id}`)
  },

  create(data) {
    return api.post(`${API_URL}/mapel`, data)
  },

  update(id, data) {
    return api.put(`${API_URL}/mapel/${id}`, data)
  },

  delete(id) {
    return api.delete(`${API_URL}/mapel/${id}`)
  },

  getGuru() {
    return api.get(`${API_URL}/guru`)
  },

  createGuru(data) {
    return api.post(`${API_URL}/guru`, data)
  },

  updateGuru(id, data) {
    return api.put(`${API_URL}/guru/${id}`, data)
  },

  attachGuru(mapelId, guruId) {
    return api.post(`${API_URL}/mapel/${mapelId}/guru/${guruId}`)
  },

  detachGuru(mapelId, guruId) {
    return api.delete(`${API_URL}/mapel/${mapelId}/guru/${guruId}`)
  }
}

export const jadwalService = {
  getAll() {
    return api.get(`${API_URL}/jadwal`)
  },

  getById(id) {
    return api.get(`${API_URL}/jadwal/${id}`)
  },

  getByKelas(kelasId) {
    return api.get(`${API_URL}/jadwal/kelas/${kelasId}`)
  },

  create(data) {
    return api.post(`${API_URL}/jadwal`, data)
  },

  update(id, data) {
    return api.put(`${API_URL}/jadwal/${id}`, data)
  },

  delete(id) {
    return api.delete(`${API_URL}/jadwal/${id}`)
  }
}

export const kegiatanService = {
  getAll() {
    return api.get(`${API_URL}/kegiatan`)
  },

  getById(id) {
    return api.get(`${API_URL}/kegiatan/${id}`)
  },

  create(data) {
    return api.post(`${API_URL}/kegiatan`, data)
  },

  update(id, data) {
    return api.put(`${API_URL}/kegiatan/${id}`, data)
  },

  delete(id) {
    return api.delete(`${API_URL}/kegiatan/${id}`)
  }
}
