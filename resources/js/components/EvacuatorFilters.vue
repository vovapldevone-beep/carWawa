<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  zones: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:filters'])

const selectedType = ref('')
const minLoadCapacity = ref('')

const typeOptions = computed(() => {
  const set = new Set()
  for (const z of props.zones) {
    const t = z.type_tow
    if (t != null && String(t).trim() !== '') {
      set.add(String(t).trim())
    }
  }
  return Array.from(set).sort((a, b) => a.localeCompare(b, 'uk'))
})

const emitFilters = () => {
  const rawMin = minLoadCapacity.value === '' ? null : Number(minLoadCapacity.value)
  const min =
    rawMin != null && !Number.isNaN(rawMin) && rawMin > 0 ? rawMin : null
  emit('update:filters', {
    typeTow: selectedType.value === '' ? null : selectedType.value,
    minLoadCapacity: min,
  })
}

watch([selectedType, minLoadCapacity], emitFilters, { immediate: true })
</script>

<template>
  <div class="evacuator-filters" aria-label="Фільтри евакуаторів">
    <h3 class="evacuator-filters__title">Фільтр евакуаторів</h3>
    <label class="evacuator-filters__field">
      <span class="evacuator-filters__label">Тип буксирування</span>
      <select v-model="selectedType" class="evacuator-filters__select">
        <option value="">Усі типи</option>
        <option v-for="t in typeOptions" :key="t" :value="t">
          {{ t }}
        </option>
      </select>
    </label>
    <label class="evacuator-filters__field">
      <span class="evacuator-filters__label">Мін. вантажопідйомність (т)</span>
      <input
        v-model="minLoadCapacity"
        type="number"
        min="0"
        step="0.1"
        class="evacuator-filters__input"
        placeholder="Без обмеження"
      />
    </label>
  </div>
</template>

<style scoped>
.evacuator-filters {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 0.5rem;
}

.evacuator-filters__title {
  margin: 0;
  font-size: 0.9375rem;
  font-weight: 600;
}

.evacuator-filters__field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.evacuator-filters__label {
  color: #555;
}

.evacuator-filters__select,
.evacuator-filters__input {
  padding: 0.35rem 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 0.875rem;
}
</style>
