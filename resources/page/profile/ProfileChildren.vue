<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Участники</p>
            <h2>Участники из вашей семьи</h2>
            <p class="profile-section__copy">Добавляйте ребёнка, выбирайте активного участника и редактируйте его данные отдельно от остального кабинета.</p>
          </div>
          <button class="profile-btn primary" @click="startCreateChild">Добавить ребёнка</button>
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
              <button v-if="child.id !== userStore.selectedChildId" class="profile-btn ghost" @click="selectChild(child.id)">Выбрать</button>
              <button class="profile-btn outline" @click="startEditChild(child)">Изменить</button>
              <button class="profile-btn danger" :disabled="deletingChildId === child.id" @click="deleteChild(child)">
                {{ deletingChildId === child.id ? '...' : 'Удалить' }}
              </button>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">Пока не добавлен ни один участник. Начните с профиля ребёнка и затем переходите к олимпиадам.</p>

        <form class="profile-form" @submit.prevent="saveChild">
          <div class="profile-section__head">
            <div>
              <p class="profile-eyebrow">{{ editingChildId ? 'Редактирование' : 'Новый участник' }}</p>
              <h3>{{ editingChildId ? 'Обновите данные ребёнка' : 'Добавьте нового ребёнка' }}</h3>
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
            <label class="profile-field wide">
              <span>Язык интерфейса</span>
              <select v-model="childForm.language_preference">
                <option value="ru">Русский</option>
                <option value="kk">Қазақша</option>
                <option value="en">English</option>
              </select>
            </label>
          </div>

          <div class="profile-form-actions">
            <button class="profile-btn primary" :disabled="savingChild">{{ savingChild ? 'Сохраняем...' : 'Сохранить профиль' }}</button>
            <button v-if="editingChildId" type="button" class="profile-btn outline" @click="resetChildForm">Отмена</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import StatusBadge from '../../components/StatusBadge.vue'
import api from '../../js/api'
import { useUserStore } from '../../stores/user'

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
    if (editingChildId.value === child.id) resetChildForm()
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
}

const saveChild = async () => {
  savingChild.value = true
  try {
    const payload = {
      ...childForm,
      grade: childForm.grade !== '' ? parseInt(childForm.grade) : null,
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

onMounted(hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
