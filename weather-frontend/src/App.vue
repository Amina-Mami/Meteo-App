<template>
  <div id="app">
    <div class="form-container">
      <h1>Météo</h1>

      <form ref="weatherForm" v-if="!editMode" @submit.prevent="createWeather">
        <input v-model="newWeather.city" placeholder="Ville" required />
        <input
          v-model.number="newWeather.temperature"
          placeholder="Température (°C)"
          required
        />
        <input
          v-model.number="newWeather.humidity"
          placeholder="Humidité (%)"
          required
        />
        <input
          v-model.number="newWeather.windSpeed"
          placeholder="Vent (km/h)"
          required
        />
        <input v-model="newWeather.date" type="datetime-local" required /><br />
        <button class="add-btn" type="submit">
          <i class="fas fa-add"></i> Ajouter
        </button>
      </form>

      <form ref="weatherForm" v-else @submit.prevent="updateWeather">
        <input v-model="selectedWeather.city" placeholder="Ville" required />
        <input
          v-model.number="selectedWeather.temperature"
          placeholder="Température (°C)"
          required
        />
        <input
          v-model.number="selectedWeather.humidity"
          placeholder="Humidité (%)"
          required
        />
        <input
          v-model.number="selectedWeather.windSpeed"
          placeholder="Vent (km/h)"
          required
        />
        <input
          v-model="selectedWeather.date"
          type="datetime-local"
          required
        /><br />
        <div class="button-group">
          <button class="edit-btn" type="submit">
            <i class="fas fa-edit"></i> Modifier
          </button>
          <button class="delete-btn" @click="cancelEdit" type="button">
            <i class="fas fa-cancel"></i> Annuler
          </button>
        </div>
      </form>
    </div>

    <div class="weather-cards-container">
      <hr />

      <div v-for="item in weatherList" :key="item.id" class="weather-card">
        <div class="left-section">
          <div class="weather-info">
            <i
              :class="getWeatherIcon(item)"
              :style="getWeatherIconStyle(item)"
              class="weather-icon"
            ></i>

            <div class="temperature">
              <p>
                <strong class="strong">{{ item.city }}</strong
                ><br />
                {{ item.temperature }}°C
              </p>
            </div>
          </div>
        </div>

        <div class="right-section">
          <p><strong>Humidité:</strong> {{ item.humidity }}%</p>
          <p><strong>Vent:</strong> {{ item.windSpeed }} km/h</p>
          <p><strong>Date:</strong> {{ formatDisplayDate(item.date) }}</p>
          <div class="button-group">
            <button class="edit-btn" @click="startEdit(item)">
              <i class="fas fa-edit"></i> Modifier
            </button>
            <button class="delete-btn" @click="deleteWeather(item.id)">
              <i class="fas fa-trash-alt"></i> Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import Swal from "sweetalert2";

interface Weather {
  id?: number;
  city: string;
  temperature: number;
  humidity: number;
  windSpeed: number;
  date: string;
}

export default defineComponent({
  name: "App",
  setup() {
    const weatherList = ref<Weather[]>([]);
    const newWeather = ref<Weather>({
      city: "",
      temperature: null,
      humidity: null,
      windSpeed: null,
      date: "",
    });

    const selectedWeather = ref<Weather | null>(null);
    const editMode = ref(false);

    const fetchWeatherData = async () => {
      try {
        const response = await fetch("http://localhost:8000/api/weather_data");
        const data = await response.json();
        weatherList.value = data;
      } catch (error) {
        console.error(
          "Erreur lors de la récupération des données météo",
          error
        );
        alert("Une erreur est survenue lors du chargement des données.");
      }
    };

    const inferWeatherType = (item: any): string => {
      const temp = item.temperature;
      const humidity = item.humidity;
      const wind = item.windSpeed;

      if (humidity > 80 && wind > 15) return "rain";
      if (temp <= 0) return "snow";
      if (temp > 25 && humidity < 60) return "sun";
      if (wind > 25) return "storm";
      if (humidity > 70) return "cloud";
      return "smog";
    };

    const getWeatherIcon = (item: any): string => {
      const type = inferWeatherType(item);
      switch (type) {
        case "rain":
          return "fas fa-cloud-showers-heavy";
        case "snow":
          return "fas fa-snowflake";
        case "storm":
          return "fas fa-bolt";
        case "cloud":
          return "fas fa-cloud";
        case "sun":
          return "fas fa-sun";
        default:
          return "fas fa-smog";
      }
    };

    const getWeatherIconStyle = (item: any): Record<string, string> => {
      const type = inferWeatherType(item);
      switch (type) {
        case "rain":
          return { color: "#2196f3" };
        case "snow":
          return { color: "#00e5ff" };
        case "storm":
          return { color: "#fdd835" };
        case "cloud":
          return { color: "#90a4ae" };
        case "sun":
          return { color: "#ff9800" };
        default:
          return { color: "#9e9e9e" };
      }
    };

    const isValidCity = (city: string): boolean => {
      const regex = /^[a-zA-Z\s]+$/;
      return regex.test(city);
    };

    const isValidWeatherData = (weather: Weather): boolean => {
      if (!isValidCity(weather.city)) return false;
      if (weather.temperature == null || isNaN(weather.temperature))
        return false;
      if (
        weather.humidity == null ||
        isNaN(weather.humidity) ||
        weather.humidity < 0 ||
        weather.humidity > 100
      )
        return false;
      if (
        weather.windSpeed == null ||
        isNaN(weather.windSpeed) ||
        weather.windSpeed < 0
      )
        return false;
      if (!weather.date) return false;
      return true;
    };

    const getValidationErrorMessage = (weather: Weather): string | null => {
      if (!isValidCity(weather.city)) {
        return "La ville ne doit contenir que des lettres.";
      }
      if (weather.temperature == null || isNaN(weather.temperature)) {
        return "La température doit être un nombre.";
      }
      if (
        weather.humidity == null ||
        isNaN(weather.humidity) ||
        weather.humidity < 0 ||
        weather.humidity > 100
      ) {
        return "L'humidité doit être un nombre entre 0 et 100.";
      }
      if (
        weather.windSpeed == null ||
        isNaN(weather.windSpeed) ||
        weather.windSpeed < 0
      ) {
        return "La vitesse du vent doit être un nombre positif.";
      }
      if (!weather.date) {
        return "Veuillez sélectionner une date.";
      }
      return null;
    };

    const showError = (message: string) => {
      Swal.fire({
        icon: "error",
        title: "Erreur",
        text: message,
        confirmButtonText: "OK",
      });
    };

    const createWeather = async () => {
      const validationError = getValidationErrorMessage(newWeather.value);
      if (validationError) {
        showError(validationError);
        return;
      }

      newWeather.value.date = new Date(newWeather.value.date).toISOString();

      try {
        const response = await fetch("http://localhost:8000/api/weather", {
          method: "POST",
          headers: { "Content-Type": "application/ld+json" },
          body: JSON.stringify(newWeather.value),
        });

        if (response.ok) {
          await fetchWeatherData();
          newWeather.value = {
            city: "",
            temperature: 0,
            humidity: 0,
            windSpeed: 0,
            date: "",
          };

          Swal.fire({
            icon: "success",
            title: "Succès",
            text: "Donnée météo créée avec succès !",
            confirmButtonText: "OK",
          });
        } else {
          const errorData = await response.json();
          console.error("Erreur API:", errorData);
          showError(`Erreur lors de la création : ${errorData.message}`);
        }
      } catch (error) {
        console.error("Erreur lors de la création des données météo", error);
        showError("Une erreur est survenue lors de la création.");
      }
    };

    const updateWeather = async () => {
      if (
        !selectedWeather.value ||
        !isValidWeatherData(selectedWeather.value)
      ) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: "Veuillez remplir correctement tous les champs.",
          confirmButtonText: "OK",
        });
        return;
      }

      if (selectedWeather.value) {
        selectedWeather.value.date = new Date(
          selectedWeather.value.date
        ).toISOString();

        const weatherToUpdate = {
          city: selectedWeather.value.city,
          temperature: selectedWeather.value.temperature,
          humidity: selectedWeather.value.humidity,
          windSpeed: selectedWeather.value.windSpeed,
          date: selectedWeather.value.date,
        };

        try {
          const response = await fetch(
            `http://localhost:8000/api/weather/${selectedWeather.value.id}`,
            {
              method: "PATCH",
              headers: {
                "Content-Type": "application/merge-patch+json",
              },
              body: JSON.stringify(weatherToUpdate),
            }
          );

          if (response.ok) {
            editMode.value = false;
            selectedWeather.value = null;
            await fetchWeatherData();

            Swal.fire({
              icon: "success",
              title: "Succès",
              text: "Donnée météo mise à jour avec succès !",
              confirmButtonText: "OK",
            });
          } else {
            const errorData = await response.json();
            Swal.fire({
              icon: "error",
              title: "Erreur",
              text: `Erreur lors de la mise à jour : ${
                errorData.message || "Erreur inconnue."
              }`,
              confirmButtonText: "OK",
            });
          }
        } catch (error) {
          console.error(
            "Erreur lors de la mise à jour des données météo",
            error
          );
          Swal.fire({
            icon: "error",
            title: "Erreur",
            text: "Une erreur est survenue lors de la mise à jour.",
            confirmButtonText: "OK",
          });
        }
      }
    };

    const deleteWeather = async (id: number) => {
      const confirmation = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action est irréversible.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler",
      });

      if (confirmation.isConfirmed) {
        try {
          const response = await fetch(
            `http://localhost:8000/api/weather/${id}`,
            {
              method: "DELETE",
            }
          );

          if (response.ok) {
            await fetchWeatherData();
            Swal.fire({
              icon: "success",
              title: "Supprimé",
              text: "Donnée météo supprimée avec succès !",
              confirmButtonText: "OK",
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Erreur",
              text: "Erreur lors de la suppression.",
              confirmButtonText: "OK",
            });
          }
        } catch (error) {
          console.error("Erreur lors de la suppression", error);
          Swal.fire({
            icon: "error",
            title: "Erreur",
            text: "Une erreur est survenue lors de la suppression.",
            confirmButtonText: "OK",
          });
        }
      }
    };
    const formatDisplayDate = (isoString: string): string => {
      const date = new Date(isoString);
      return date.toLocaleString();
    };

    const startEdit = (item: Weather) => {
      const formattedDate = new Date(item.date).toISOString().slice(0, 16);

      selectedWeather.value = {
        ...item,
        date: formattedDate,
      };

      editMode.value = true;

      const form = document.querySelector("form");
      if (form) {
        form.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    };

    const cancelEdit = () => {
      editMode.value = false;
      selectedWeather.value = null;
    };

    onMounted(() => {
      fetchWeatherData();
    });

    return {
      weatherList,
      newWeather,
      selectedWeather,
      editMode,
      createWeather,
      deleteWeather,
      startEdit,
      cancelEdit,
      updateWeather,
      formatDisplayDate,
      getWeatherIcon,
      getWeatherIconStyle,
      inferWeatherType,
      isValidWeatherData,
      getValidationErrorMessage,
    };
  },
});
</script>

<style scoped>
#app {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
  font-family: "Poppins", sans-serif;
  min-height: 100vh;
  color: white;
  display: flex;
  justify-content: space-between;
}

.form-container {
  flex: 1;
  margin-right: 20px;
  margin-left: 80px;
}

.weather-cards-container {
  flex: 2;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
}

.weather-card {
  background: rgb(116, 158, 168);
  border-radius: 12px;
  margin-top: 10px;

  padding: 15px;
  margin-bottom: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.weather-card:hover {
  transform: translateY(-5px);
}

.left-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  font-size: 1.5rem;
  color: #333;
}

.weather-info {
  display: flex;
  align-items: center;
  gap: 50px;
}

.right-section {
  display: flex;
  flex-direction: column;

  flex: 1;
  font-size: 0.9rem;
}

.weather-icon {
  font-size: 3rem;
}

.temperature {
  font-size: 1.8rem;
  font-weight: bold;
}

button i {
  margin-right: 5px;
}

button:hover {
  background-color: #00b8b0;
  cursor: pointer;
}
.add-btn {
  background-color: aquamarine;
}

form input {
  margin: 5px 0;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 100%;
}

form {
  margin-bottom: 20px;
}

form input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

button {
  background-color: #00dfd8;
  border: none;
  border-radius: 12px;
  width: 50%;
  color: black;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 10px;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
}
.button-group {
  display: flex;
  justify-content: flex-start;
  gap: 10px;
  margin-left: 40px;
  margin-top: 10px;
}

.edit-btn {
  margin-right: 0px;
  margin-top: 10px;
  background-color: rgb(72, 42, 80);
}
.delete-btn {
  margin-right: 10px;
  margin-top: 10px;
  background-color: rgb(168, 73, 73);
}
.strong {
  font-size: 3rem !important;
}
</style>
