<template>
  <v-app>
    <!-- Header moderne -->
    <v-app-bar 
      color="white" 
      elevation="1" 
      height="80"
      class="px-4"
    >
      <div class="d-flex align-center w-100">
        <!-- Logo et titre -->
        <div class="d-flex align-center">
          <div class="logo-container mr-3">
            <v-icon size="40" color="orange-darken-2">mdi-chef-hat</v-icon>
          </div>
          <div>
            <h1 class="text-h4 font-weight-bold text-grey-darken-3 mb-0">CookBook</h1>
            <p class="text-caption text-grey mb-0">Vos recettes préférées</p>
          </div>
        </div>

        <v-spacer />

        <!-- Barre de recherche dans le header -->
        <div class="search-container" style="max-width: 400px; width: 100%;">
          <v-text-field
            v-model="globalSearch"
            placeholder="Rechercher une recette..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            class="search-field"
            @input="handleGlobalSearch"
          />
        </div>

        <v-spacer />

        <!-- Bouton d'ajout -->
        <v-btn
          color="orange-darken-2"
          variant="flat"
          size="large"
          @click="showAddForm"
          prepend-icon="mdi-plus"
          class="text-white font-weight-medium"
        >
          Nouvelle recette
        </v-btn>
      </div>
    </v-app-bar>

    <v-main class="bg-grey-lighten-5">
      <!-- Hero section -->
      <div v-if="!showForm && recipes.length === 0" class="hero-section">
        <v-container>
          <div class="text-center py-16">
            <v-icon size="120" color="orange-darken-2" class="mb-6">mdi-chef-hat</v-icon>
            <h2 class="text-h3 font-weight-bold text-grey-darken-3 mb-4">
              Bienvenue dans votre livre de recettes
            </h2>
            <p class="text-h6 text-grey-darken-1 mb-8">
              Créez, organisez et partagez vos recettes préférées
            </p>
            <v-btn
              color="orange-darken-2"
              size="x-large"
              variant="flat"
              @click="showAddForm"
              prepend-icon="mdi-plus"
              class="text-white px-8"
            >
              Créer ma première recette
            </v-btn>
          </div>
        </v-container>
      </div>

      <v-container class="py-8" v-else>
        <!-- Alertes -->
        <v-alert
          v-if="alert.show"
          :type="alert.type"
          dismissible
          @click:close="alert.show = false"
          class="mb-6"
          variant="tonal"
        >
          {{ alert.message }}
        </v-alert>

        <!-- Formulaire -->
        <RecipeForm
          v-if="showForm"
          :recipe="selectedRecipe"
          :is-editing="isEditing"
          @save="handleSaveRecipe"
          @cancel="handleCancelForm"
          @error="handleError"
        />

        <!-- Liste des recettes -->
        <div v-if="!showForm">
          <!-- Stats et filtres -->
          <div class="d-flex justify-space-between align-center mb-6">
            <div>
              <h2 class="text-h4 font-weight-bold text-grey-darken-3">
                Mes Recettes
              </h2>
              <p class="text-body-1 text-grey-darken-1">
                {{ filteredRecipes.length }} recette{{ filteredRecipes.length > 1 ? 's' : '' }} trouvée{{ filteredRecipes.length > 1 ? 's' : '' }}
              </p>
            </div>
            
            <!-- Filtres rapides -->
            <div class="d-flex gap-2">
              <v-chip-group v-model="selectedFilter" color="orange-darken-2">
                <v-chip value="all" variant="outlined">Toutes</v-chip>
                <v-chip value="recent" variant="outlined">Récentes</v-chip>
                <v-chip value="favorites" variant="outlined">Favoris</v-chip>
              </v-chip-group>
            </div>
          </div>

          <RecipeList
            :recipes="filteredRecipes"
            :loading="loading"
            @edit="handleEditRecipe"
            @delete="handleDeleteRecipe"
            @error="handleError"
          />
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import RecipeList from './components/RecipeList.vue'
import RecipeForm from './components/RecipeForm.vue'

// État de l'application
const recipes = ref([])
const loading = ref(false)
const showForm = ref(false)
const isEditing = ref(false)
const selectedRecipe = ref(null)
const globalSearch = ref('')
const selectedFilter = ref('all')

// Gestion des alertes
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// URL de base de l'API
const API_BASE_URL = 'http://localhost:8000/api/recipes'

// Recettes filtrées
const filteredRecipes = computed(() => {
  let filtered = recipes.value

  // Filtrage par recherche globale
  if (globalSearch.value) {
    const query = globalSearch.value.toLowerCase()
    filtered = filtered.filter(recipe => 
      recipe.title.toLowerCase().includes(query) ||
      recipe.description.toLowerCase().includes(query) ||
      recipe.ingredients.some(ing => ing.toLowerCase().includes(query))
    )
  }

  // Filtrage par catégorie
  switch (selectedFilter.value) {
    case 'recent':
      filtered = filtered.slice().sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      break
    case 'favorites':
      filtered = filtered.filter(recipe => recipe.is_favorite)
      break
  }

  return filtered
})

// Fonction pour afficher une alerte
const showAlert = (type, message) => {
  alert.value = {
    show: true,
    type,
    message
  }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}

// Gestion de la recherche globale
const handleGlobalSearch = () => {
  // La recherche est réactive via le computed
}

// Charger toutes les recettes
const fetchRecipes = async () => {
  loading.value = true
  try {
    const response = await fetch(API_BASE_URL)
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }
    const data = await response.json()
    recipes.value = data
  } catch (error) {
    console.error('Erreur lors du chargement des recettes:', error)
    showAlert('error', 'Erreur lors du chargement des recettes')
  } finally {
    loading.value = false
  }
}

// Afficher le formulaire d'ajout
const showAddForm = () => {
  selectedRecipe.value = null
  isEditing.value = false
  showForm.value = true
}

// Gérer l'édition d'une recette
const handleEditRecipe = (recipe) => {
  selectedRecipe.value = { ...recipe }
  isEditing.value = true
  showForm.value = true
}

// Gérer la sauvegarde d'une recette
const handleSaveRecipe = async (recipeData) => {
  try {
    let response
    
    if (isEditing.value) {
      response = await fetch(`${API_BASE_URL}/${recipeData.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(recipeData)
      })
    } else {
      response = await fetch(API_BASE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(recipeData)
      })
    }

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    const savedRecipe = await response.json()
    
    if (isEditing.value) {
      const index = recipes.value.findIndex(r => r.id === savedRecipe.id)
      if (index !== -1) {
        recipes.value[index] = savedRecipe
      }
      showAlert('success', '✨ Recette modifiée avec succès!')
    } else {
      recipes.value.push(savedRecipe)
      showAlert('success', '🎉 Nouvelle recette ajoutée!')
    }

    showForm.value = false
    selectedRecipe.value = null
    isEditing.value = false

  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    showAlert('error', 'Erreur lors de la sauvegarde de la recette')
  }
}

// Gérer l'annulation du formulaire
const handleCancelForm = () => {
  showForm.value = false
  selectedRecipe.value = null
  isEditing.value = false
}

// Gérer la suppression d'une recette
const handleDeleteRecipe = async (recipeId) => {
  try {
    const response = await fetch(`${API_BASE_URL}/${recipeId}`, {
      method: 'DELETE'
    })

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    recipes.value = recipes.value.filter(recipe => recipe.id !== recipeId)
    showAlert('success', '🗑️ Recette supprimée')

  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    showAlert('error', 'Erreur lors de la suppression de la recette')
  }
}

// Gérer les erreurs des composants enfants
const handleError = (message) => {
  showAlert('error', message)
}

// Charger les recettes au montage du composant
onMounted(() => {
  fetchRecipes()
})
</script>

<style scoped>
.logo-container {
  background: linear-gradient(135deg, #FF8A65 0%, #FF7043 100%);
  border-radius: 12px;
  padding: 8px;
  box-shadow: 0 4px 12px rgba(255, 112, 67, 0.3);
}

.search-field :deep(.v-field) {
  border-radius: 25px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.hero-section {
  background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
  min-height: 60vh;
  display: flex;
  align-items: center;
}

.v-container {
  max-width: 1400px;
}
</style>