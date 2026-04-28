<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Участники</p>
            <h2>Участники профиля</h2>
            <p class="profile-section__copy">
              Добавляйте участников, выбирайте активный профиль и при необходимости обновляйте данные отдельно.
            </p>
          </div>
          <button class="profile-btn primary" @click="startCreateChild">Добавить участника</button>
        </div>

        <div v-if="children.length" class="profile-card-grid">
          <article
            v-for="child in children"
            :key="child.id"
            class="profile-list-card"
            :class="{ selected: child.id === userStore.selectedChildId }"
          >
            <div class="profile-card-head">
              <div>
                <h3>{{ child.full_name }}</h3>
                <p>{{ child.grade ? `${child.grade} класс` : 'Класс не указан' }}</p>
              </div>
              <StatusBadge
                :label="child.id === userStore.selectedChildId ? 'Выбран' : 'Готов к участию'"
                :tone="child.id === userStore.selectedChildId ? 'success' : 'neutral'"
              />
            </div>
            <p>{{ child.school || 'Школа не указана' }} · {{ child.city || 'Город не указан' }}</p>
            <div class="profile-actions-row">
              <button
                v-if="child.id !== userStore.selectedChildId"
                class="profile-btn ghost"
                @click="selectChild(child.id)"
              >
                Выбрать участника для олимпиады
              </button>
              <button class="profile-btn outline" @click="startEditChild(child)">Изменить</button>
              <button class="profile-btn danger" :disabled="deletingChildId === child.id" @click="deleteChild(child)">
                {{ deletingChildId === child.id ? '...' : 'Удалить' }}
              </button>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">
          Участники пока не добавлены. Сначала сохраните профиль участника, затем переходите к выбору олимпиады.
        </p>

        <form id="participant-form" class="profile-form" @submit.prevent="saveChild">
          <div class="profile-section__head">
            <div>
              <p class="profile-eyebrow">{{ editingChildId ? 'Редактирование' : 'Новый участник' }}</p>
              <h3>{{ editingChildId ? 'Обновите данные участника' : 'Добавьте участника' }}</h3>
            </div>
          </div>

          <div class="profile-form-grid">
            <label class="profile-field"><span>Имя</span><input v-model="childForm.first_name" placeholder="Имя" required /></label>
            <label class="profile-field"><span>Фамилия</span><input v-model="childForm.last_name" placeholder="Фамилия" required /></label>
            <label class="profile-field"><span>Дата рождения</span><input v-model="childForm.birth_date" type="date" /></label>
            <label class="profile-field">
              <span>Класс</span>
              <select v-model="childForm.grade">
                <option value="">Выберите класс</option>
                <option v-for="grade in gradeOptions" :key="grade" :value="String(grade)">{{ grade }} класс</option>
              </select>
            </label>
            <label class="profile-field"><span>Школа</span><input v-model="childForm.school" placeholder="Школа" /></label>
            <label class="profile-field"><span>Город</span><input v-model="childForm.city" placeholder="Город" /></label>
          </div>

          <div class="profile-form-actions">
            <button class="profile-btn primary" :disabled="savingChild">{{ savingChild ? 'Сохраняем...' : 'Сохранить данные' }}</button>
            <button v-if="editingChildId" type="button" class="profile-btn outline" @click="resetChildForm">Отмена</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import StatusBadge from '../../components/StatusBadge.vue'
import api from '../../js/api'
import { useUserStore } from '../../stores/user'

const route = useRoute()
const userStore = useUserStore()
const children = ref([])
const editingChildId = ref(null)
const savingChild = ref(false)
const deletingChildId = ref(null)
const gradeOptions = [3, 4, 5, 6, 7, 8, 9, 10, 11]

const childForm = reactive({
  first_name: '',
  last_name: '',
  birth_date: '',
  grade: '',
  school: '',
  city: '',
  language_preference: 'ru',
})

const scrollToForm = async () => {
  await nextTick()
  const target = document.getElementById('participant-form')
  if (!target) return

  const headerOffset = document.querySelector('.header')?.offsetHeight ?? 72
  const top = target.getBoundingClientRect().top + window.scrollY - headerOffset - 16
  window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' })
}

const resetChildForm = () => {
  editingChildId.value = null
  childForm.first_name = ''
  childForm.last_name = ''
  childForm.birth_date = ''
  childForm.grade = ''
  childForm.school = userStore.user?.school || ''
  childForm.city = userStore.user?.city || ''
  childForm.language_preference = 'ru'
}

const hydrate = async () => {
  await userStore.fetchUser()
  children.value = userStore.children
  resetChildForm()
}

const selectChild = (childId) => {
  userStore.setSelectedChild(childId)
}

const startCreateChild = () => {
  resetChildForm()
  scrollToForm()
}

const deleteChild = async (child) => {
  if (!confirm(`Удалить участника «${child.full_name}»? Это действие нельзя отменить.`)) return
  deletingChildId.value = child.id

  try {
    await api.delete(`/profile/children/${child.id}`)

    if (userStore.selectedChildId === child.id) {
      userStore.setSelectedChild(null)
    }

    await userStore.fetchUser()
    children.value = userStore.children

    if (!userStore.selectedChildId && userStore.children.length) {
      userStore.setSelectedChild(userStore.children[0].id)
    }

    if (editingChildId.value === child.id) {
      resetChildForm()
    }
  } finally {
    deletingChildId.value = null
  }
}

const startEditChild = (child) => {
  editingChildId.value = child.id
  childForm.first_name = child.first_name
  childForm.last_name = child.last_name
  childForm.birth_date = child.birth_date || ''
  childForm.grade = child.grade || ''
  childForm.school = child.school || ''
  childForm.city = child.city || ''
  childForm.language_preference = child.language_preference || 'ru'
  scrollToForm()
}

const saveChild = async () => {
  savingChild.value = true

  try {
    const payload = {
      ...childForm,
      grade: childForm.grade !== '' ? parseInt(childForm.grade, 10) : null,
      birth_date: childForm.birth_date || null,
    }

    if (editingChildId.value) {
      await api.put(`/profile/children/${editingChildId.value}`, payload)
    } else {
      await api.post('/profile/children', payload)
    }

    await userStore.fetchUser()
    children.value = userStore.children

    if (!userStore.selectedChildId && userStore.children.length) {
      userStore.setSelectedChild(userStore.children[0].id)
    }

    resetChildForm()
  } finally {
    savingChild.value = false
  }
}

onMounted(async () => {
  await hydrate()

  if (route.hash === '#participant-form') {
    scrollToForm()
  }
})
</script>

<style src="../../css/profile-hub.css"></style>
