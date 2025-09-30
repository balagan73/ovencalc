// --- Constants ---
const MIN_EFFICIENCY = 0.78;          // Minimal oven efficiency
const HA = 4.16;                       // Lower heating value of wood, kWh/kg
const THEORETICAL_AIR_VOLUME = 4;      // Nm3/kg
const ACCESS_AIR_FACTOR = 2.95;
const THEORETICAL_FLUE_GAS_VOLUME = 4.8; // Nm3/kg
const AIR_DENSITY = 1.293;             // kg/Nm3
const FLUE_GAS_DENSITY = 1.282;        // kg/Nm3
const OPTIMAL_FIRING_PERIOD = 77;      // minutes
const MAX_CO_EMISSION = 1500;          // mg/Nm3
const MAX_NOX_EMISSION = 225;          // mg/Nm3
const MAX_SOOT_EMISSION = 120;         // mg/Nm3
const MAX_PARTICULATE_EMISSION = 90;   // mg/Nm3
const MAX_GLASS_RATIO = 1/6;
const MIN_COMBUSTION_CHAMBER_SIDE = 23; // cm

let hasCookingPlate = false;
let hasWaterExchange = false;
let minAirIntakeHeight = 5; // cm

document.addEventListener('DOMContentLoaded', function() {
    // Select the calculate button
    var calculateButton = document.getElementById('calculate');

    // Add click event listener
    calculateButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent form submission
        console.log("DOING");
        // Get values from the form
        var power = parseFloat(document.getElementById('power').value) || 0;
        var heatingPeriod = parseFloat(document.getElementById('heating_period').value) || 0;
        var singleWall = document.getElementById('single_wall').checked;
        var circumference = parseFloat(document.getElementById('circumference').value) || 0;
        var altitude = document.getElementById('altitude').value || 0;

        // Call your calculation function (you'll need a JS version of calculateAll)
        // Example:
        var results = calculateAll(power, heatingPeriod, circumference, singleWall, altitude);

        // Display results
        var resultsDiv = document.getElementById('results');
        resultsDiv.textContent = 'Results: ' + JSON.stringify(results, null, 2);
    });
});

// --- Main calculation function ---
function calculateAll(power, heatingPeriod, combustionChamberCircumference, singleWall = true, altitude = 100) {
    const maxWood = calculateMaxWood(power, heatingPeriod);
    const minWood = maxWood * 0.5;
    const minimalOvenMass = maxWood * 50; // kg

    const combustionChamberSurfaceRequirement = calculateCombustionChamberSurfaceRequirement(maxWood);
    const minimalCombustionChamberArea = calculateMinCombustionChamberArea(maxWood);
    const combustionChamberOptimalHeight = calculateCombustionChamberOptimalHeight(maxWood, combustionChamberCircumference);
    const combustionChamberMinimalHeight = calculateCombustionChamberMinimalHeight(maxWood);
    const combustionChamberMaxArea = calculateCombustionChamberMaxArea(maxWood, combustionChamberCircumference);
    
    const combustionChamberSideLength = (-2 * combustionChamberMinimalHeight + 
        Math.sqrt((2 * combustionChamberMinimalHeight) ** 2 + 1800 * maxWood)) / 2;
    
    const minimalPassageLength = (singleWall ? 1.3 : 1.5) * Math.sqrt(maxWood);
    const pilotBurnerArea = maxWood;

    const temperature = 23; // Celsius

    const tempCorrectionFactor = calculateTempCorrectionFactor(temperature);
    const altitudeCorrectionFactor = calculateAltCorrectionFactor(altitude);
    const airIntakeFlowRate = calculateAirIntakeFlowRate(maxWood, temperature, altitude);
    const flueGasFlowRate = calculateFlueGasFlowRate(maxWood, temperature, altitude);
    const flueGasMassFlowRate = 0.0035 * maxWood;
    const realAirDensity = calculateRealDensity(AIR_DENSITY, temperature, altitude);
    const realFlueDensity = calculateRealDensity(FLUE_GAS_DENSITY, temperature, altitude);

    return {
        maxWood,
        minWood,
        minimalOvenMass,
        combustionChamberSurfaceRequirement,
        minimalCombustionChamberArea,
        combustionChamberOptimalHeight,
        combustionChamberMinimalHeight,
        combustionChamberMaxArea,
        combustionChamberSideLength,
        minimalPassageLength,
        pilotBurnerArea,
        airIntakeFlowRate,
        flueGasFlowRate,
        flueGasMassFlowRate,
        realAirDensity,
        realFlueDensity
    };
}

// --- Helper functions ---
function calculateMaxWood(power, heatingPeriod) {
    return power * heatingPeriod / (HA * MIN_EFFICIENCY);
}

function calculateCombustionChamberSurfaceRequirement(maxWood) {
    return 900 * maxWood;
}

function calculateMinCombustionChamberArea(maxWood) {
    return 100 * maxWood;
}

function calculateCombustionChamberOptimalHeight(maxWood, circumference) {
    return (900 * maxWood - 2 * calculateMinCombustionChamberArea(maxWood)) / circumference;
}

function calculateCombustionChamberMinimalHeight(maxWood) {
    return maxWood + 25;
}

function calculateCombustionChamberMaxArea(maxWood, circumference) {
    return 900 * maxWood - calculateCombustionChamberMinimalHeight(maxWood) * circumference / 2;
}

function calculateAirIntakeFlowRate(maxWood, temp, alt) {
    return 0.00256 * maxWood * calculateTempCorrectionFactor(temp) * calculateAltCorrectionFactor(alt);
}

function calculateFlueGasFlowRate(maxWood, temp, alt) {
    return 0.00273 * maxWood * calculateTempCorrectionFactor(temp) * calculateAltCorrectionFactor(alt);
}

function calculateRealDensity(density, temp, alt) {
    return density / (calculateTempCorrectionFactor(temp) * calculateAltCorrectionFactor(alt));
}

function calculateTempCorrectionFactor(temp) {
    return (273 + temp) / 273;
}

function calculateAltCorrectionFactor(alt) {
    return 1 / Math.exp(-9.81 * alt / 78624);
}
