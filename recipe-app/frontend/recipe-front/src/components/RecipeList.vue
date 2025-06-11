<template>
    <div>
      <!-- Indicateur de chargement -->
      <div v-if="loading" class="text-center py-12">
        <v-progress-circular
          indeterminate
          color="orange-darken-2"
          size="64"
          width="6"
        />
        <p class="text-h6 text-grey-darken-1 mt-4">Chargement des recettes...</p>
      </div>
  
      <!-- Message si aucune recette -->
      <div v-else-if="recipes.length === 0" class="text-center py-16">
        <v-icon size="80" color="grey-lighten-1" class="mb-4">mdi-book-open-variant</v-icon>
        <h3 class="text-h5 text-grey-darken-1 mb-2">Aucune recette trouvée</h3>
        <p class="text-body-1 text-grey">Commencez par ajouter votre première recette !</p>
      </div>
  
      <!-- Grille des recettes -->
      <div v-else class="recipes-grid">
        <div
          v-for="recipe in recipes"
          :key="recipe.id"
          class="recipe-card-container"
        >
          <v-card
            class="recipe-card"
            elevation="0"
            hover
            @click="$emit('edit', recipe)"
          >
            <!-- Image de la recette -->
            <div class="recipe-image">
              <v-img
                :src="recipe.image || '/placeholder.svg?height=200&width=300'"
                height="200"
                cover
                class="recipe-img"
              >
                <div class="image-overlay">
                  <v-chip
                    color="white"
                    text-color="grey-darken-3"
                    size="small"
                    class="ma-2"
                  >
                    <v-icon start size="16">mdi-clock-outline</v-icon>
                    {{ recipe.prep_time || '30' }} min
                  </v-chip>
                </div>
              </v-img>
            </div>
  
            <!-- Contenu de la carte -->
            <v-card-text class="pa-4">
              <!-- Titre et rating -->
              <div class="d-flex justify-space-between align-start mb-2">
                <h3 class="text-h6 font-weight-bold text-grey-darken-3 line-clamp-2">
                  {{ recipe.title }}
                </h3>
                <div class="ml-2">
                  <v-rating
                    :model-value="recipe.rating || 4.5"
                    color="amber"
                    density="compact"
                    size="small"
                    readonly
                    half-increments
                  />
                </div>
              </div>
  
              <!-- Description -->
              <p class="text-body-2 text-grey-darken-1 mb-3 line-clamp-2">
                {{ recipe.description }}
              </p>
  
              <!-- Métadonnées -->
              <div class="d-flex align-center mb-3 text-caption text-grey-darken-1">
                <v-icon size="16" class="mr-1">mdi-account-outline</v-icon>
                {{ recipe.servings || 4 }} portions
                <v-divider vertical class="mx-2" />
                <v-icon size="16" class="mr-1">mdi-signal</v-icon>
                {{ recipe.difficulty || 'Facile' }}
              </div>
  
              <!-- Ingrédients principaux -->
              <div class="ingredients-preview mb-3">
                <v-chip-group>
                  <v-chip
                    v-for="(ingredient, index) in recipe.ingredients.slice(0, 3)"
                    :key="index"
                    size="x-small"
                    color="orange-lighten-4"
                    text-color="orange-darken-3"
                    variant="flat"
                  >
                    {{ ingredient }}
                  </v-chip>
                  <v-chip
                    v-if="recipe.ingredients.length > 3"
                    size="x-small"
                    color="grey-lighten-2"
                    text-color="grey-darken-2"
                    variant="flat"
                  >
                    +{{ recipe.ingredients.length - 3 }}
                  </v-chip>
                </v-chip-group>
              </div>
            </v-card-text>
  
            <!-- Actions -->
            <v-card-actions class="px-4 pb-4 pt-0">
              <v-btn
                color="orange-darken-2"
                variant="text"
                @click.stop="$emit('edit', recipe)"
                prepend-icon="mdi-pencil"
                size="small"
              >
                Modifier
              </v-btn>
              
              <v-spacer />
              
              <v-btn
                icon="mdi-heart-outline"
                variant="text"
                color="grey-darken-1"
                size="small"
                @click.stop="toggleFavorite(recipe)"
              />
              
              <v-btn
                icon="mdi-delete-outline"
                variant="text"
                color="grey-darken-1"
                size="small"
                @click.stop="confirmDelete(recipe)"
              />
            </v-card-actions>
          </v-card>
        </div>
      </div>
  
      <!-- Dialog de confirmation de suppression -->
      <v-dialog v-model="deleteDialog" max-width="400">
        <v-card class="text-center pa-4">
          <v-icon size="64" color="error" class="mb-4">mdi-delete-alert</v-icon>
          
          <v-card-title class="text-h5 mb-2">
            Supprimer la recette ?
          </v-card-title>
          
          <v-card-text class="text-body-1">
            Êtes-vous sûr de vouloir supprimer "<strong>{{ recipeToDelete?.title }}</strong>" ?
            <br>Cette action est irréversible.
          </v-card-text>
          
          <v-card-actions class="justify-center pt-4">
            <v-btn
              color="grey"
              variant="outlined"
              @click="deleteDialog = false"
              class="mr-2"
            >
              Annuler
            </v-btn>
            <v-btn
              color="error"
              variant="flat"
              @click="deleteRecipe"
            >
              Supprimer
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  // Props
  const props = defineProps({
    recipes: {
      type: Array,
      required: true
    },
    loading: {
      type: Boolean,
      default: false
    }
  })
  
  // Émissions d'événements
  const emit = defineEmits(['edit', 'delete', 'error'])
  
  // État local
  const deleteDialog = ref(false)
  const recipeToDelete = ref(null)
  
  // Confirmer la suppression
  const confirmDelete = (recipe) => {
    recipeToDelete.value = recipe
    deleteDialog.value = true
  }
  
  // Supprimer la recette
  const deleteRecipe = () => {
    if (recipeToDelete.value) {
      emit('delete', recipeToDelete.value.id)
      deleteDialog.value = false
      recipeToDelete.value = null
    }
  }
  
  // Toggle favori (fonctionnalité future)
  const toggleFavorite = (recipe) => {
    // À implémenter avec l'API
    console.log('Toggle favorite for:', recipe.title)
  }
  </script>
  
  <style scoped>
  .recipes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    padding: 8px;
  }
  
  .recipe-card-container {
    height: 100%;
  }
  
  .recipe-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    border-radius: 16px !important;
    border: 1px solid #f0f0f0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
  }
  
  .recipe-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12) !important;
    border-color: #e0e0e0;
  }
  
  .recipe-image {
    position: relative;
    overflow: hidden;
    border-radius: 16px 16px 0 0;
  }
  
  .recipe-img {
    transition: transform 0.3s ease;
  }
  
  .recipe-card:hover .recipe-img {
    transform: scale(1.05);
  }
  
  .image-overlay {
    position: absolute;
    top: 0;
    right: 0;
    background: linear-gradient(135deg, transparent 0%, rgba(0,0,0,0.1) 100%);
  }
  
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  .ingredients-preview {
    min-height: 32px;
  }
  
  .v-card-text {
    flex-grow: 1;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .recipes-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }
  
  @media (min-width: 1200px) {
    .recipes-grid {
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
  }
  </style>