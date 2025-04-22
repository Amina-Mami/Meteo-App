# WeatherApp

WeatherApp est une application permettant de récupérer les données météo d'une ville via l'API d'Open-Meteo et de les gérer ( CRUD) , avec un back-end en Symfony et un front-end en Vue.js.

## Structure du projet

Le projet est divisé en deux parties :
- `weather-backend` : le back-end Symfony
- `weather-frontend` : le front-end Vue.js

## Prérequis

- PHP 
- Composer
- Node.js 
- NPM

## Installation

### 1. Cloner le dépôt
Clonez le dépôt Git :
git clone https://github.com/USERNAME/WeatherApp.git <br>


### 2. Allez dans le dossier weather-backend :
cd weather-backend <br>
composer install <br>
symfony server:start <br>

### 3. Allez dans le dossier weather-frontend :
cd weather-frontend <br>
npm install <br>
npm run dev
