<template>
    <v-card class="recipe-form-card" elevation="2">
      <!-- Header du formulaire -->
      <div class="form-header">
        <div class="d-flex align-center">
          <div class="form-icon mr-4">
            <v-icon size="32" color="white">
              {{ isEditing ? 'mdi-pencil' : 'mdi-plus' }}
            </v-icon>
          </div>
          <div>
            <h2 class="text-h4 font-weight-bold text-white mb-1">
              {{ isEditing ? 'Modifier la recette' : 'Nouvelle recette' }}
            </h2>
            <p class="text-body-1 text-white opacity-90 mb-0">
              {{ isEditing ? 'Apportez vos modifications' : 'Partagez votre création culinaire' }}
            </p>
          </div>
        </div>
      </div>
  
      <v-card-text class="pa-8">
        <v-form ref="formRef" v-model="formValid" @submit.prevent="submitForm">
          <v-row>
            <!-- Section informations principales -->
            <v-col cols="12">
              <div class="section-header mb-6">
                <h3 class="text-h5 font-weight-bold text-grey-darken-3 mb-2">
                  <v-icon color="orange-darken-2" class="mr-2">mdi-information</v-icon>
                  Informations principales
                </h3>
                <v-divider color="orange-darken-2" thickness="2" class="divider-accent" />
              </div>
            </v-col>
  
            <!-- Titre -->
            <v-col cols="12" md="8">
              <v-text-field
                v-model="form.title"
                label="Titre de la recette"
                :rules="titleRules"
                variant="outlined"
                required
                prepend-inner-icon="mdi-food"
                placeholder="Ex: Tarte aux pommes de grand-mère"
                class="form-field"
              />
            </v-col>
  
            <!-- Temps de préparation -->
            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.prep_time"
                label="Temps (minutes)"
                type="number"
                variant="outlined"
                prepend-inner-icon="mdi-clock-outline"
                placeholder="30"
                class="form-field"
              />
            </v-col>
  
            <!-- Description -->
            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="Description"
                :rules="descriptionRules"
                variant="outlined"
                rows="4"
                required
                prepend-inner-icon="mdi-text"
                placeholder="Décrivez votre recette, son origine, ses particularités..."
                hint="Une description attrayante donnera envie de tester votre recette"
                persistent-hint
                class="form-field"
              />
            </v-col>
  
            <!-- Métadonnées -->
            <v-col cols="12" md="6">
              <v-select
                v-model="form.difficulty"
                label="Difficulté"
                :items="difficultyOptions"
                variant="outlined"
                prepend-inner-icon="mdi-signal"
                class="form-field"
              />
            </v-col>
  
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.servings"
                label="Nombre de portions"
                type="number"
                variant="outlined"
                prepend-inner-icon="mdi-account-group"
                placeholder="4"
                class="form-field"
              />
            </v-col>
  
            <!-- Section ingrédients -->
            <v-col cols="12" class="mt-6">
              <div class="section-header mb-6">
                <h3 class="text-h5 font-weight-bold text-grey-darken-3 mb-2">
                  <v-icon color="orange-darken-2" class="mr-2">mdi-format-list-bulleted</v-icon>
                  Ingrédients
                </h3>
                <v-divider color="orange-darken-2" thickness="2" class="divider-accent" />
                <p class="text-body-2 text-grey-darken-1 mt-2">
                  Listez tous les ingrédients nécessaires. Au moins un ingrédient est requis.
                </p>
              </div>
  
              <!-- Liste des ingrédients -->
              <div class="ingredients-container">
                <v-row
                  v-for="(ingredient, index) in form.ingredients"
                  :key="index"
                  class="ingredient-row mb-3"
                  align="center"
                >
                  <v-col cols="10">
                    <v-text-field
                      v-model="form.ingredients[index]"
                      :label="`Ingrédient ${index + 1}`"
                      variant="outlined"
                      density="comfortable"
                      :rules="ingredientRules"
                      prepend-inner-icon="mdi-food-variant"
                      placeholder="Ex: 200g de farine"
                      class="ingredient-field"
                    />
                  </v-col>
                  <v-col cols="2" class="text-center">
                    <v-btn
                      color="error"
                      icon="mdi-delete"
                      variant="text"
                      @click="removeIngredient(index)"
                      :disabled="form.ingredients.length === 1"
                      class="delete-ingredient-btn"
                    />
                  </v-col>
                </v-row>
  
                <!-- Bouton ajouter ingrédient -->
                <v-btn
                  color="orange-darken-2"
                  variant="outlined"
                  @click="addIngredient"
                  prepend-icon="mdi-plus"
                  class="add-ingredient-btn mb-4"
                  block
                >
                  Ajouter un ingrédient
                </v-btn>
  
                <!-- Message d'erreur pour les ingrédients -->
                <v-alert
                  v-if="ingredientsError"
                  type="error"
                  variant="tonal"
                  density="compact"
                  class="mb-4"
                >
                  {{ ingredientsError }}
                </v-alert>
              </div>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
  
      <!-- Actions -->
      <v-card-actions class="pa-8 pt-0">
        <v-btn
          color="grey-darken-1"
          variant="outlined"
          size="large"
          @click="cancelForm"
          prepend-icon="mdi-cancel"
          class="mr-4"
        >
          Annuler
        </v-btn>
        
        <v-spacer />
        
        <v-btn
          color="orange-darken-2"
          variant="flat"
          size="large"
          @click="submitForm"
          :loading="submitting"
          :disabled="!canSubmit"
          prepend-icon="mdi-content-save"
          class="text-white px-8"
        >
          {{ isEditing ? 'Sauvegarder' : 'Créer la recette' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </template>
  
  <script setup>
  import { ref, computed, watch, nextTick } from 'vue'
  
  // Props
  const props = defineProps({
    recipe: {
      type: Object,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    }
  })
  
  const emit = defineEmits(['save', 'cancel', 'error'])
  
  const formRef = ref(null)
  const formValid = ref(false)
  const submitting = ref(false)
  
  const difficultyOptions = [
    { title: 'Très facile', value: 'Très facile' },
    { title: 'Facile', value: 'Facile' },
    { title: 'Moyen', value: 'Moyen' },
    { title: 'Difficile', value: 'Difficile' },
    { title: 'Très difficile', value: 'Très difficile' }
  ]
  
  // Formulaire
  const form = ref({
    title: '',
    description: '',
    ingredients: [''],
    prep_time: 30,
    difficulty: 'Facile',
    servings: 4
  })
  
  const ingredientsError = ref('')
  
  const titleRules = [
    v => !!v || 'Le titre est requis',
    v => (v && v.length >= 3) || 'Le titre doit contenir au moins 3 caractères',
    v => (v && v.length <= 100) || 'Le titre ne peut pas dépasser 100 caractères'
  ]
  
  const descriptionRules = [
    v => !!v || 'La description est requise',
    v => (v && v.length >= 10) || 'La description doit contenir au moins 10 caractères',
    v => (v && v.length <= 1000) || 'La description ne peut pas dépasser 1000 caractères'
  ]
  
  const ingredientRules = [
    v => !!v || 'L\'ingrédient ne peut pas être vide',
    v => (v && v.length >= 2) || 'L\'ingrédient doit contenir au moins 2 caractères'
  ]
  
  const validateIngredients = () => {
    const validIngredients = form.value.ingredients.filter(ing => ing.trim() !== '')
    
    if (validIngredients.length === 0) {
      ingredientsError.value = 'Au moins un ingrédient est requis'
      return false
    }
    
    const uniqueIngredients = [...new Set(validIngredients.map(ing => ing.trim().toLowerCase()))]
    if (uniqueIngredients.length !== validIngredients.length) {
      ingredientsError.value = 'Les ingrédients ne peuvent pas être dupliqués'
      return false
    }
    
    ingredientsError.value = ''
    return true
  }
  
  const canSubmit = computed(() => {
    return formValid.value && validateIngredients() && !submitting.value
  })
  
  const addIngredient = () => {
    form.value.ingredients.push('')
  }
  
  const removeIngredient = (index) => {
    if (form.value.ingredients.length > 1) {
      form.value.ingredients.splice(index, 1)
    }
  }
  
  const submitForm = async () => {
    if (!formRef.value) return
    
    const { valid } = await formRef.value.validate()
    
    if (!valid || !validateIngredients()) {
      return
    }
  
    submitting.value = true
  
    try {
      const cleanIngredients = form.value.ingredients
        .map(ing => ing.trim())
        .filter(ing => ing !== '')
  
      const recipeData = {
        title: form.value.title.trim(),
        description: form.value.description.trim(),
        ingredients: cleanIngredients,
        prep_time: parseInt(form.value.prep_time) || 30,
        difficulty: form.value.difficulty,
        servings: parseInt(form.value.servings) || 4
      }
  
      if (props.isEditing && props.recipe) {
        recipeData.id = props.recipe.id
      }
  
      emit('save', recipeData)
    } catch (error) {
      emit('error', 'Erreur lors de la sauvegarde de la recette')
    } finally {
      submitting.value = false
    }
  }
  
  const cancelForm = () => {
    resetForm()
    emit('cancel')
  }
  
  const resetForm = () => {
    form.value = {
      title: '',
      description: '',
      ingredients: [''],
      prep_time: 30,
      difficulty: 'Facile',
      servings: 4
    }
    ingredientsError.value = ''
    if (formRef.value) {
      formRef.value.resetValidation()
    }
  }
  
  // Charger les données de la recette si on modifie
  const loadRecipeData = () => {
    if (props.recipe && props.isEditing) {
      form.value = {
        title: props.recipe.title || '',
        description: props.recipe.description || '',
        ingredients: props.recipe.ingredients && props.recipe.ingredients.length > 0 
          ? [...props.recipe.ingredients] 
          : [''],
        prep_time: props.recipe.prep_time || 30,
        difficulty: props.recipe.difficulty || 'Facile',
        servings: props.recipe.servings || 4
      }
    } else {
      resetForm()
    }
  }
  
  // Watchers
  watch(() => props.recipe, () => {
    nextTick(() => {
      loadRecipeData()
    })
  }, { immediate: true })
  
  watch(() => props.isEditing, (newVal) => {
    if (!newVal) {
      resetForm()
    }
  })
  </script>
  
  <style scoped>
  .recipe-form-card {
    border-radius: 20px !important;
    overflow: hidden;
    max-width: 900px;
    margin: 0 auto;
  }
  
  .form-header {
    background: linear-gradient(135deg, #FF8A65 0%, #FF7043 100%);
    padding: 32px;
  }
  
  .form-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 12px;
    backdrop-filter: blur(10px);
  }
  
  .section-header {
    margin-bottom: 24px;
  }
  
  .divider-accent {
    width: 60px;
  }
  
  .form-field :deep(.v-field) {
    border-radius: 12px;
  }
  
  .ingredients-container {
    background: #fafafa;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e0e0e0;
  }
  
  .ingredient-row {
    background: white;
    border-radius: 12px;
    padding: 8px;
    margin-bottom: 12px;
    border: 1px solid #f0f0f0;
  }
  
  .ingredient-field :deep(.v-field) {
    border-radius: 8px;
  }
  
  .add-ingredient-btn {
    border-radius: 12px !important;
    border-style: dashed !important;
    border-width: 2px !important;
    height: 48px;
  }
  
  .delete-ingredient-btn:hover {
    background-color: rgba(244, 67, 54, 0.1);
  }
  
  @media (max-width: 768px) {
    .form-header {
      padding: 24px 16px;
    }
    
    .v-card-text {
      padding: 24px 16px !important;
    }
    
    .v-card-actions {
      padding: 24px 16px !important;
    }
  }
  </style>