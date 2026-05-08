import fs from "node:fs";

const outPath = "docs/top-50-games-union.md";

const norm = (s) =>
  String(s || "")
    .toLowerCase()
    .replace(/[’‘]/g, "'")
    .replace(/&/g, "and")
    .replace(/\(.*?\)/g, " ")
    .replace(/[^a-z0-9]+/g, " ")
    .trim()
    .replace(/\s+/g, " ");

const aliasGroupsRaw = [
  ["DuckTales", "Duck Tales"],
  ["Mike Tyson's Punch-Out!!", "Punch-Out!!"],
  ["Zelda II: The Adventure of Link", "Adventures of Link"],
  ["R.C. Pro-Am", "RC Pro-Am"],
  ["Double Dragon II: The Revenge", "Double Dragon II"],
  ["Contra III: The Alien Wars", "Contra III"],
  [
    "TMNT IV: Turtles in Time",
    "Teenage Mutant Ninja Turtles IV",
    "Turtles in Time",
    "TMNT IV",
    "Turtles in Time (TMNT IV)",
  ],
  ["StarTropics", "Star Tropics"],
  ["The Guardian Legend", "Guardian Legend"],
  ["Life Force", "Lifeforce"],
  [
    "Teenage Mutant Ninja Turtles II",
    "TMNT II: The Arcade Game",
    "Teenage Mutant Ninja Turtles II: The Arcade Game",
  ],
  ["Tetris (Nintendo)", "Tetris (BPS)", "Tetris"],
  ["Dr. Mario", "Dr Mario"],

  ["Super Mario RPG", "Super Mario RPG: Legend of the Seven Stars"],
  ["Donkey Kong Country 2", "Donkey Kong Country 2: Diddy's Kong Quest"],
  ["Super Mario World 2: Yoshi's Island", "Yoshi's Island"],
  ["Castlevania IV", "Super Castlevania IV"],
  ["U.N. Squadron", "UN Squadron"],

  ["Seaquest", "Sea Quest"],

  ["Street Fighter II", "Street Fighter II: World Warrior"],
  ["The Simpsons Arcade", "The Simpsons Arcade Game"],
  ["X-Men", "X-Men (6-Player)"],
  ["OutRun", "Out Run"],
  ["King of Fighters '98", "The King of Fighters '98", "The King of Fighters '98"],
  ["Robotron 2084", "Robotron: 2084"],
  ["1943", "1943: The Battle of Midway"],

  ["Sid Meier's Civilization", "Civilization"],
  ["Star Wars: TIE Fighter", "TIE Fighter", "Tie Fighter"],
  ["Doom II: Hell on Earth", "Doom 2", "Doom II"],
  ["Master of Orion II", "Master of Orion II: Battle at Antares"],
  ["Day of the Tentacle", "Maniac Mansion: Day of the Tentacle"],
  ["Indiana Jones and the Fate of Atlantis", "Fate of Atlantis"],
  ["Command & Conquer: Red Alert", "C&C: Red Alert"],
  ["Warcraft II: Tides of Darkness", "Warcraft II"],
  ["Ultima VII: The Black Gate", "Ultima VII"],
  ["Gabriel Knight: Sins of the Fathers", "Gabriel Knight"],
  ["Commander Keen 4: Goodbye, Galaxy!", "Commander Keen 4"],
];

const aliasGroups = aliasGroupsRaw.map((g) => [...new Set(g.map(norm))]);

// If a title belongs to an alias group, prefer the most complete (longest)
// human-readable name from that alias group as the display title.
const aliasDisplayTitleByKey = new Map();
for (const group of aliasGroupsRaw) {
  const normed = [...new Set(group.map(norm))];
  const key = normed.slice().sort().join("|");
  const display = group.slice().sort((a, b) => b.length - a.length)[0];
  aliasDisplayTitleByKey.set(key, display);
}

function aliasKeyForNorm(n) {
  for (const g of aliasGroups) {
    if (g.includes(n)) return g.slice().sort().join("|");
  }
  return null;
}

function pickTitle(existing, incoming) {
  if (!existing) return incoming;
  // Prefer the most complete original name (longest) across sources.
  // If tied, prefer S1.
  if (incoming.title.length > existing.title.length) return incoming;
  if (existing.title.length > incoming.title.length) return existing;
  if (existing.source === "S1" && incoming.source !== "S1") return existing;
  if (incoming.source === "S1" && existing.source !== "S1") return incoming;
  return existing;
}

function mergeTop50({ s1, s2 }) {
  const byKey = new Map();

  const add = (row, source) => {
    const n = norm(row.title);
    const k = aliasKeyForNorm(n) || n;
    const displayTitle = aliasDisplayTitleByKey.get(k) ?? row.title;

    const cur = byKey.get(k) || {
      key: k,
      titleChoice: null,
      bestRank: row.rank,
      bestScore: row.score,
      s1: null,
      s2: null,
      sources: new Set(),
    };

    cur.sources.add(source);
    cur.titleChoice = pickTitle(cur.titleChoice, { title: displayTitle, source });
    cur.bestRank = Math.min(cur.bestRank ?? Infinity, row.rank);
    cur.bestScore = Math.max(cur.bestScore ?? -Infinity, row.score);
    if (source === "S1") cur.s1 = row;
    if (source === "S2") cur.s2 = row;

    byKey.set(k, cur);
  };

  s1.forEach((r) => add(r, "S1"));
  s2.forEach((r) => add(r, "S2"));

  const merged = [...byKey.values()].map((v) => ({
    title: v.titleChoice.title,
    bestRank: v.bestRank,
    bestScore: v.bestScore,
    s1: v.s1 ? `${v.s1.rank} / ${v.s1.score}` : "-",
    s2: v.s2 ? `${v.s2.rank} / ${v.s2.score}` : "-",
  }));

  merged.sort(
    (a, b) =>
      a.bestRank - b.bestRank || b.bestScore - a.bestScore || a.title.localeCompare(b.title),
  );

  return merged.slice(0, 50);
}

// Source lists (S1 = consensus Top 50, S2 = retro Top 50)
// Embedded directly to avoid shell/JSON quoting issues.
const data = {
  NES: {
    name: "NES",
    s1: [
      { rank: 1, title: "Super Mario Bros. 3", score: 100.0 },
      { rank: 2, title: "The Legend of Zelda", score: 98.5 },
      { rank: 3, title: "Super Mario Bros.", score: 97.2 },
      { rank: 4, title: "Mike Tyson's Punch-Out!!", score: 96.0 },
      { rank: 5, title: "Mega Man 2", score: 95.1 },
      { rank: 6, title: "Contra", score: 94.3 },
      { rank: 7, title: "Metroid", score: 93.8 },
      { rank: 8, title: "Castlevania III: Dracula's Curse", score: 92.5 },
      { rank: 9, title: "Final Fantasy", score: 91.9 },
      { rank: 10, title: "Super Mario Bros. 2", score: 91.0 },
      { rank: 11, title: "River City Ransom", score: 89.8 },
      { rank: 12, title: "Mega Man 3", score: 89.2 },
      { rank: 13, title: "Kirby's Adventure", score: 88.5 },
      { rank: 14, title: "Zelda II: The Adventure of Link", score: 87.9 },
      { rank: 15, title: "Ninja Gaiden", score: 87.2 },
      { rank: 16, title: "DuckTales", score: 86.5 },
      { rank: 17, title: "Castlevania", score: 85.9 },
      { rank: 18, title: "Tetris (Nintendo)", score: 85.1 },
      { rank: 19, title: "Bionic Commando", score: 84.4 },
      { rank: 20, title: "Crystalis", score: 83.8 },
      { rank: 21, title: "Dragon Warrior IV", score: 83.1 },
      { rank: 22, title: "Blaster Master", score: 82.5 },
      { rank: 23, title: "StarTropics", score: 81.9 },
      { rank: 24, title: "Tecmo Super Bowl", score: 81.2 },
      { rank: 25, title: "Bubble Bobble", score: 80.6 },
      { rank: 26, title: "Battletoads", score: 79.9 },
      { rank: 27, title: "Double Dragon II: The Revenge", score: 79.3 },
      { rank: 28, title: "Dragon Warrior III", score: 78.7 },
      { rank: 29, title: "Maniac Mansion", score: 78.0 },
      { rank: 30, title: "Life Force", score: 77.4 },
      { rank: 31, title: "Kid Icarus", score: 76.8 },
      { rank: 32, title: "Ninja Gaiden II", score: 76.1 },
      { rank: 33, title: "Teenage Mutant Ninja Turtles II", score: 75.5 },
      { rank: 34, title: "Faxanadu", score: 74.9 },
      { rank: 35, title: "Rygar", score: 74.2 },
      { rank: 36, title: "Guardian Legend", score: 73.6 },
      { rank: 37, title: "Gradius", score: 73.0 },
      { rank: 38, title: "Excitebike", score: 72.3 },
      { rank: 39, title: "Dr. Mario", score: 71.7 },
      { rank: 40, title: "Duck Hunt", score: 71.1 },
      { rank: 41, title: "Metal Gear", score: 70.4 },
      { rank: 42, title: "Shadowgate", score: 69.8 },
      { rank: 43, title: "Vice: Project Doom", score: 69.2 },
      { rank: 44, title: "Little Samson", score: 68.5 },
      { rank: 45, title: "G.I. Joe: A Real American Hero", score: 67.9 },
      { rank: 46, title: "Shatterhand", score: 67.3 },
      { rank: 47, title: "Batman: The Video Game", score: 66.6 },
      { rank: 48, title: "Adventures of Lolo", score: 66.0 },
      { rank: 49, title: "RC Pro-Am", score: 65.4 },
      { rank: 50, title: "Super C", score: 64.7 }
    ],
    s2: [
      { rank: 1, title: "Super Mario Bros.", score: 99 },
      { rank: 2, title: "The Legend of Zelda", score: 98 },
      { rank: 3, title: "Mega Man 2", score: 97 },
      { rank: 4, title: "Contra", score: 96 },
      { rank: 5, title: "Castlevania", score: 95 },
      { rank: 6, title: "Tecmo Super Bowl", score: 94 },
      { rank: 7, title: "Metroid", score: 93 },
      { rank: 8, title: "Ninja Gaiden", score: 92 },
      { rank: 9, title: "Duck Tales", score: 91 },
      { rank: 10, title: "Battletoads", score: 90 },
      { rank: 11, title: "Punch-Out!!", score: 89 },
      { rank: 12, title: "Bionic Commando", score: 88 },
      { rank: 13, title: "Final Fantasy", score: 87 },
      { rank: 14, title: "Kirby's Adventure", score: 86 },
      { rank: 15, title: "Chip 'n Dale Rescue Rangers", score: 85 },
      { rank: 16, title: "Excitebike", score: 84 },
      { rank: 17, title: "Blaster Master", score: 83 },
      { rank: 18, title: "Ice Climber", score: 82 },
      { rank: 19, title: "Ghosts 'n Goblins", score: 81 },
      { rank: 20, title: "Double Dragon II", score: 80 },
      { rank: 21, title: "River City Ransom", score: 79 },
      { rank: 22, title: "Tetris (BPS)", score: 78 },
      { rank: 23, title: "Adventures of Link", score: 77 },
      { rank: 24, title: "Rad Racer", score: 76 },
      { rank: 25, title: "Star Tropics", score: 75 },
      { rank: 26, title: "RC Pro-Am", score: 74 },
      { rank: 27, title: "Balloon Fight", score: 73 },
      { rank: 28, title: "Jackal", score: 72 },
      { rank: 29, title: "Life Force", score: 71 },
      { rank: 30, title: "Kid Icarus", score: 70 },
      { rank: 31, title: "Mega Man 3", score: 69 },
      { rank: 32, title: "TMNT II: The Arcade Game", score: 68 },
      { rank: 33, title: "Darkwing Duck", score: 67 },
      { rank: 34, title: "Little Samson", score: 67 },
      { rank: 35, title: "Mega Man 4", score: 66 },
      { rank: 36, title: "Super Mario Bros. 3", score: 66 },
      { rank: 37, title: "Double Dragon", score: 65 },
      { rank: 38, title: "Gradius", score: 64 },
      { rank: 39, title: "Lifeforce", score: 63 },
      { rank: 40, title: "Tecmo Bowl", score: 63 },
      { rank: 41, title: "Batman: The Video Game", score: 62 },
      { rank: 42, title: "Little Nemo: The Dream Master", score: 61 },
      { rank: 43, title: "Faxanadu", score: 61 },
      { rank: 44, title: "Willow", score: 60 },
      { rank: 45, title: "Gimmick!", score: 60 },
      { rank: 46, title: "The Guardian Legend", score: 59 },
      { rank: 47, title: "Crystalis", score: 59 },
      { rank: 48, title: "Shadowgate", score: 58 },
      { rank: 49, title: "Panic Restaurant", score: 57 },
      { rank: 50, title: "Bucky O'Hare", score: 57 }
    ]
  },
  SNES: {
    name: "SNES",
    s1: [
      { rank: 1, title: "The Legend of Zelda: A Link to the Past", score: 100.0 },
      { rank: 2, title: "Chrono Trigger", score: 99.2 },
      { rank: 3, title: "Super Metroid", score: 98.6 },
      { rank: 4, title: "Super Mario World", score: 97.9 },
      { rank: 5, title: "Final Fantasy VI", score: 97.2 },
      { rank: 6, title: "Super Mario RPG", score: 95.8 },
      { rank: 7, title: "Donkey Kong Country 2", score: 95.1 },
      { rank: 8, title: "Mega Man X", score: 94.4 },
      { rank: 9, title: "EarthBound", score: 93.7 },
      { rank: 10, title: "Super Mario World 2: Yoshi's Island", score: 93.0 },
      { rank: 11, title: "Street Fighter II Turbo", score: 92.3 },
      { rank: 12, title: "Super Mario Kart", score: 91.6 },
      { rank: 13, title: "Donkey Kong Country", score: 90.9 },
      { rank: 14, title: "Secret of Mana", score: 90.2 },
      { rank: 15, title: "Castlevania IV", score: 89.5 },
      { rank: 16, title: "Contra III: The Alien Wars", score: 88.8 },
      { rank: 17, title: "Final Fantasy IV", score: 88.1 },
      { rank: 18, title: "Kirby Super Star", score: 87.4 },
      { rank: 19, title: "Lufia II: Rise of the Sinistrals", score: 86.7 },
      { rank: 20, title: "Terranigma", score: 86.0 },
      { rank: 21, title: "TMNT IV: Turtles in Time", score: 85.3 },
      { rank: 22, title: "Star Fox", score: 84.6 },
      { rank: 23, title: "F-Zero", score: 83.9 },
      { rank: 24, title: "Super Punch-Out!!", score: 83.2 },
      { rank: 25, title: "Illusion of Gaia", score: 82.5 },
      { rank: 26, title: "Zombies Ate My Neighbors", score: 81.8 },
      { rank: 27, title: "Mega Man X2", score: 81.1 },
      { rank: 28, title: "ActRaiser", score: 80.4 },
      { rank: 29, title: "Harvest Moon", score: 79.7 },
      { rank: 30, title: "Breath of Fire II", score: 79.0 },
      { rank: 31, title: "Killer Instinct", score: 78.3 },
      { rank: 32, title: "Ogre Battle", score: 77.6 },
      { rank: 33, title: "NBA Jam: T.E.", score: 76.9 },
      { rank: 34, title: "Soul Blazer", score: 76.2 },
      { rank: 35, title: "Secret of Evermore", score: 75.5 },
      { rank: 36, title: "U.N. Squadron", score: 74.8 },
      { rank: 37, title: "Gradius III", score: 74.1 },
      { rank: 38, title: "Super Ghouls 'n Ghosts", score: 73.4 },
      { rank: 39, title: "Demon's Crest", score: 72.7 },
      { rank: 40, title: "Sunset Riders", score: 72.0 },
      { rank: 41, title: "Wild Guns", score: 71.3 },
      { rank: 42, title: "SimCity", score: 70.6 },
      { rank: 43, title: "Shadowrun", score: 69.9 },
      { rank: 44, title: "Mortal Kombat II", score: 69.2 },
      { rank: 45, title: "Pilotwings", score: 68.5 },
      { rank: 46, title: "Tetris Attack", score: 67.8 },
      { rank: 47, title: "Disney's Aladdin", score: 67.1 },
      { rank: 48, title: "Earthworm Jim", score: 66.4 },
      { rank: 49, title: "Magical Quest", score: 65.7 },
      { rank: 50, title: "Super Bomberman 2", score: 65.0 }
    ],
    s2: [
      { rank: 1, title: "Super Mario World", score: 99 },
      { rank: 2, title: "The Legend of Zelda: A Link to the Past", score: 99 },
      { rank: 3, title: "Super Metroid", score: 98 },
      { rank: 4, title: "Chrono Trigger", score: 98 },
      { rank: 5, title: "Final Fantasy VI", score: 97 },
      { rank: 6, title: "Donkey Kong Country", score: 96 },
      { rank: 7, title: "Super Mario Kart", score: 95 },
      { rank: 8, title: "Street Fighter II Turbo", score: 94 },
      { rank: 9, title: "Mega Man X", score: 93 },
      { rank: 10, title: "Yoshi's Island", score: 93 },
      { rank: 11, title: "EarthBound", score: 92 },
      { rank: 12, title: "F-Zero", score: 91 },
      { rank: 13, title: "Contra III", score: 90 },
      { rank: 14, title: "Teenage Mutant Ninja Turtles IV", score: 89 },
      { rank: 15, title: "Super Castlevania IV", score: 88 },
      { rank: 16, title: "ActRaiser", score: 87 },
      { rank: 17, title: "Killer Instinct", score: 86 },
      { rank: 18, title: "Mortal Kombat II", score: 85 },
      { rank: 19, title: "Star Fox", score: 85 },
      { rank: 20, title: "Gradius III", score: 84 },
      { rank: 21, title: "Pilotwings", score: 83 },
      { rank: 22, title: "Super Punch-Out!!", score: 82 },
      { rank: 23, title: "Breath of Fire II", score: 81 },
      { rank: 24, title: "Secret of Mana", score: 80 },
      { rank: 25, title: "NBA Jam", score: 79 },
      { rank: 26, title: "UN Squadron", score: 78 },
      { rank: 27, title: "Shadowrun", score: 77 },
      { rank: 28, title: "Cybernator", score: 76 },
      { rank: 29, title: "Axelay", score: 75 },
      { rank: 30, title: "Soul Blazer", score: 74 },
      { rank: 31, title: "Mega Man X2", score: 73 },
      { rank: 32, title: "Donkey Kong Country 2", score: 73 },
      { rank: 33, title: "Super Mario RPG", score: 72 },
      { rank: 34, title: "Terranigma", score: 72 },
      { rank: 35, title: "Final Fantasy IV", score: 71 },
      { rank: 36, title: "Tales of Phantasia", score: 70 },
      { rank: 37, title: "Illusion of Gaia", score: 70 },
      { rank: 38, title: "Knights of the Round", score: 69 },
      { rank: 39, title: "Super Ghouls 'n Ghosts", score: 68 },
      { rank: 40, title: "Tactics Ogre", score: 68 },
      { rank: 41, title: "Harvest Moon", score: 67 },
      { rank: 42, title: "Fire Emblem: Mystery of the Emblem", score: 67 },
      { rank: 43, title: "Super Bomberman", score: 66 },
      { rank: 44, title: "Ogre Battle", score: 65 },
      { rank: 45, title: "Kirby Super Star", score: 65 },
      { rank: 46, title: "Stunt Race FX", score: 64 },
      { rank: 47, title: "Super Street Fighter II", score: 63 },
      { rank: 48, title: "Pocky & Rocky", score: 63 },
      { rank: 49, title: "Demon's Crest", score: 62 },
      { rank: 50, title: "The Mask of the Phantasm", score: 61 }
    ]
  },
  atari2600: {
    name: "Atari 2600",
    s1: [
      { rank: 1, title: "Pitfall!", score: 100.0 },
      { rank: 2, title: "Adventure", score: 98.4 },
      { rank: 3, title: "River Raid", score: 97.1 },
      { rank: 4, title: "Ms. Pac-Man", score: 96.2 },
      { rank: 5, title: "Space Invaders", score: 95.5 },
      { rank: 6, title: "Yars' Revenge", score: 94.8 },
      { rank: 7, title: "Asteroids", score: 93.9 },
      { rank: 8, title: "Donkey Kong", score: 93.1 },
      { rank: 9, title: "Demon Attack", score: 92.4 },
      { rank: 10, title: "Berzerk", score: 91.7 },
      { rank: 11, title: "Centipede", score: 90.9 },
      { rank: 12, title: "Frogger", score: 90.2 },
      { rank: 13, title: "Kaboom!", score: 89.5 },
      { rank: 14, title: "Missile Command", score: 88.7 },
      { rank: 15, title: "Dig Dug", score: 88.0 },
      { rank: 16, title: "Warlords", score: 87.3 },
      { rank: 17, title: "H.E.R.O.", score: 86.5 },
      { rank: 18, title: "Jungle Hunt", score: 85.8 },
      { rank: 19, title: "Keystone Kapers", score: 85.1 },
      { rank: 20, title: "Seaquest", score: 84.3 },
      { rank: 21, title: "Phoenix", score: 83.6 },
      { rank: 22, title: "Beamrider", score: 82.9 },
      { rank: 23, title: "Starmaster", score: 82.1 },
      { rank: 24, title: "Enduro", score: 81.4 },
      { rank: 25, title: "Breakout", score: 80.7 },
      { rank: 26, title: "Haunted House", score: 79.9 },
      { rank: 27, title: "Battlezone", score: 79.2 },
      { rank: 28, title: "Q*bert", score: 78.5 },
      { rank: 29, title: "Millipede", score: 77.7 },
      { rank: 30, title: "Wizard of Wor", score: 77.0 },
      { rank: 31, title: "California Games", score: 76.3 },
      { rank: 32, title: "Mario Bros.", score: 75.5 },
      { rank: 33, title: "Robot Tank", score: 74.8 },
      { rank: 34, title: "Combat", score: 74.1 },
      { rank: 35, title: "Defender", score: 73.3 },
      { rank: 36, title: "Joust", score: 72.6 },
      { rank: 37, title: "Frostbite", score: 71.9 },
      { rank: 38, title: "Dragonfire", score: 71.1 },
      { rank: 39, title: "Megamania", score: 70.4 },
      { rank: 40, title: "Vanguard", score: 69.7 },
      { rank: 41, title: "Super Breakout", score: 68.9 },
      { rank: 42, title: "Kung-Fu Master", score: 68.2 },
      { rank: 43, title: "Solaris", score: 67.5 },
      { rank: 44, title: "Tapper", score: 66.7 },
      { rank: 45, title: "Spider-Man", score: 66.0 },
      { rank: 46, title: "Empire Strikes Back", score: 65.3 },
      { rank: 47, title: "Midnight Magic", score: 64.5 },
      { rank: 48, title: "Ice Hockey", score: 63.8 },
      { rank: 49, title: "Fast Food", score: 63.1 },
      { rank: 50, title: "Video Pinball", score: 62.4 }
    ],
    s2: [
      { rank: 1, title: "Pitfall!", score: 99 },
      { rank: 2, title: "Space Invaders", score: 98 },
      { rank: 3, title: "River Raid", score: 97 },
      { rank: 4, title: "Asteroids", score: 96 },
      { rank: 5, title: "Pac-Man", score: 94 },
      { rank: 6, title: "Missile Command", score: 93 },
      { rank: 7, title: "Breakout", score: 92 },
      { rank: 8, title: "Combat", score: 91 },
      { rank: 9, title: "Adventure", score: 90 },
      { rank: 10, title: "Yars' Revenge", score: 89 },
      { rank: 11, title: "Haunted House", score: 88 },
      { rank: 12, title: "Centipede", score: 87 },
      { rank: 13, title: "Frogger", score: 86 },
      { rank: 14, title: "Demon Attack", score: 85 },
      { rank: 15, title: "Defender", score: 84 },
      { rank: 16, title: "Kaboom!", score: 83 },
      { rank: 17, title: "Berzerk", score: 82 },
      { rank: 18, title: "Phoenix", score: 81 },
      { rank: 19, title: "Cosmic Ark", score: 80 },
      { rank: 20, title: "Atlantis", score: 79 },
      { rank: 21, title: "Pitfall II", score: 78 },
      { rank: 22, title: "Jungle Hunt", score: 77 },
      { rank: 23, title: "Q*bert", score: 76 },
      { rank: 24, title: "Stampede", score: 75 },
      { rank: 25, title: "H.E.R.O.", score: 74 },
      { rank: 26, title: "Warlords", score: 73 },
      { rank: 27, title: "Night Driver", score: 72 },
      { rank: 28, title: "Solaris", score: 71 },
      { rank: 29, title: "Keystone Kapers", score: 70 },
      { rank: 30, title: "Starmaster", score: 69 },
      { rank: 31, title: "Enduro", score: 68 },
      { rank: 32, title: "Seaquest", score: 68 },
      { rank: 33, title: "Barnstorming", score: 67 },
      { rank: 34, title: "Skiing", score: 66 },
      { rank: 35, title: "Venture", score: 66 },
      { rank: 36, title: "Vanguard", score: 65 },
      { rank: 37, title: "Freeway", score: 64 },
      { rank: 38, title: "Dodge 'Em", score: 63 },
      { rank: 39, title: "Video Pinball", score: 63 },
      { rank: 40, title: "Activision Tennis", score: 62 },
      { rank: 41, title: "Fishing Derby", score: 61 },
      { rank: 42, title: "Chopper Command", score: 61 },
      { rank: 43, title: "Dragster", score: 60 },
      { rank: 44, title: "Grand Prix", score: 59 },
      { rank: 45, title: "Skiing (Activision)", score: 59 },
      { rank: 46, title: "Pressure Cooker", score: 58 },
      { rank: 47, title: "Spider Fighter", score: 57 },
      { rank: 48, title: "Megamania", score: 57 },
      { rank: 49, title: "Laser Blast", score: 56 },
      { rank: 50, title: "Ice Hockey", score: 55 }
    ]
  },
  arcade: {
    name: "Arcade / MAME",
    s1: [
      { rank: 1, title: "Street Fighter II", score: 100.0 },
      { rank: 2, title: "Pac-Man", score: 99.1 },
      { rank: 3, title: "Donkey Kong", score: 98.3 },
      { rank: 4, title: "Galaga", score: 97.6 },
      { rank: 5, title: "Ms. Pac-Man", score: 96.9 },
      { rank: 6, title: "Metal Slug", score: 96.1 },
      { rank: 7, title: "Turtles in Time", score: 95.4 },
      { rank: 8, title: "Space Invaders", score: 94.7 },
      { rank: 9, title: "The Simpsons Arcade", score: 93.9 },
      { rank: 10, title: "NBA Jam", score: 93.2 },
      { rank: 11, title: "Mortal Kombat II", score: 92.5 },
      { rank: 12, title: "X-Men", score: 91.7 },
      { rank: 13, title: "Asteroids", score: 91.0 },
      { rank: 14, title: "Golden Axe", score: 90.3 },
      { rank: 15, title: "Dig Dug", score: 89.5 },
      { rank: 16, title: "Final Fight", score: 88.8 },
      { rank: 17, title: "Bubble Bobble", score: 88.1 },
      { rank: 18, title: "Frogger", score: 87.3 },
      { rank: 19, title: "Metal Slug 3", score: 86.6 },
      { rank: 20, title: "OutRun", score: 85.9 },
      { rank: 21, title: "Joust", score: 85.1 },
      { rank: 22, title: "Marvel vs. Capcom", score: 84.4 },
      { rank: 23, title: "Gauntlet", score: 83.7 },
      { rank: 24, title: "Robotron 2084", score: 82.9 },
      { rank: 25, title: "Defender", score: 82.2 },
      { rank: 26, title: "Contra", score: 81.5 },
      { rank: 27, title: "Centipede", score: 80.7 },
      { rank: 28, title: "Sunset Riders", score: 80.0 },
      { rank: 29, title: "Double Dragon", score: 79.3 },
      { rank: 30, title: "Q*bert", score: 78.5 },
      { rank: 31, title: "Ghouls 'n Ghosts", score: 77.8 },
      { rank: 32, title: "Tekken 3", score: 77.1 },
      { rank: 33, title: "Daytona USA", score: 76.3 },
      { rank: 34, title: "Time Pilot", score: 75.6 },
      { rank: 35, title: "Paperboy", score: 74.9 },
      { rank: 36, title: "1943", score: 74.1 },
      { rank: 37, title: "R-Type", score: 73.4 },
      { rank: 38, title: "Rastan", score: 72.7 },
      { rank: 39, title: "Tapper", score: 71.9 },
      { rank: 40, title: "Black Tiger", score: 71.2 },
      { rank: 41, title: "Cadillacs and Dinosaurs", score: 70.5 },
      { rank: 42, title: "King of Fighters '98", score: 69.7 },
      { rank: 43, title: "Killer Instinct", score: 69.0 },
      { rank: 44, title: "Arkanoid", score: 68.3 },
      { rank: 45, title: "BurgerTime", score: 67.5 },
      { rank: 46, title: "Shinobi", score: 66.8 },
      { rank: 47, title: "Elevator Action", score: 66.1 },
      { rank: 48, title: "Captain Commando", score: 65.3 },
      { rank: 49, title: "Strikers 1945", score: 64.6 },
      { rank: 50, title: "Battle Circuit", score: 63.9 }
    ],
    s2: [
      { rank: 1, title: "Street Fighter II: World Warrior", score: 99 },
      { rank: 2, title: "Metal Slug", score: 99 },
      { rank: 3, title: "Pac-Man", score: 98 },
      { rank: 4, title: "Donkey Kong", score: 98 },
      { rank: 5, title: "Galaga", score: 97 },
      { rank: 6, title: "Mortal Kombat II", score: 96 },
      { rank: 7, title: "Cadillacs and Dinosaurs", score: 95 },
      { rank: 8, title: "The King of Fighters '98", score: 95 },
      { rank: 9, title: "1942", score: 94 },
      { rank: 10, title: "Final Fight", score: 93 },
      { rank: 11, title: "Bubble Bobble", score: 92 },
      { rank: 12, title: "Contra", score: 91 },
      { rank: 13, title: "Turtles in Time (TMNT IV)", score: 91 },
      { rank: 14, title: "Gunstar Heroes", score: 90 },
      { rank: 15, title: "Golden Axe", score: 89 },
      { rank: 16, title: "Frogger", score: 88 },
      { rank: 17, title: "Dig Dug", score: 87 },
      { rank: 18, title: "Rastan", score: 86 },
      { rank: 19, title: "Double Dragon", score: 85 },
      { rank: 20, title: "Qix", score: 84 },
      { rank: 21, title: "R-Type", score: 83 },
      { rank: 22, title: "Xevious", score: 82 },
      { rank: 23, title: "Gyruss", score: 81 },
      { rank: 24, title: "Altered Beast", score: 80 },
      { rank: 25, title: "Neo Geo: Samurai Shodown II", score: 79 },
      { rank: 26, title: "Ghouls 'n Ghosts", score: 78 },
      { rank: 27, title: "Sunset Riders", score: 77 },
      { rank: 28, title: "Battletoads (Arcade)", score: 76 },
      { rank: 29, title: "Strikers 1945", score: 75 },
      { rank: 30, title: "Mars Matrix", score: 74 },
      { rank: 31, title: "Metal Slug 3", score: 74 },
      { rank: 32, title: "Street Fighter Alpha 2", score: 73 },
      { rank: 33, title: "The King of Fighters '99", score: 73 },
      { rank: 34, title: "Dungeons & Dragons: Tower of Doom", score: 72 },
      { rank: 35, title: "Captain Commando", score: 71 },
      { rank: 36, title: "Snow Bros.", score: 70 },
      { rank: 37, title: "Puzzle Bobble", score: 70 },
      { rank: 38, title: "Magical Drop III", score: 69 },
      { rank: 39, title: "1943: The Battle of Midway", score: 68 },
      { rank: 40, title: "Gauntlet", score: 68 },
      { rank: 41, title: "Joust", score: 67 },
      { rank: 42, title: "Robotron: 2084", score: 67 },
      { rank: 43, title: "Tapper", score: 66 },
      { rank: 44, title: "Sinistar", score: 65 },
      { rank: 45, title: "Tempest", score: 65 },
      { rank: 46, title: "Neo Geo: Last Blade 2", score: 64 },
      { rank: 47, title: "Splatterhouse", score: 63 },
      { rank: 48, title: "Undercover Cops", score: 63 },
      { rank: 49, title: "Teenage Mutant Ninja Turtles (1989)", score: 62 },
      { rank: 50, title: "Pang", score: 61 }
    ]
  },
  msdos: {
    name: "MS-DOS",
    s1: [
      { rank: 1, title: "Doom", score: 100.0 },
      { rank: 2, title: "Sid Meier's Civilization", score: 98.9 },
      { rank: 3, title: "SimCity 2000", score: 97.7 },
      { rank: 4, title: "The Secret of Monkey Island", score: 96.6 },
      { rank: 5, title: "Master of Orion II", score: 95.5 },
      { rank: 6, title: "System Shock", score: 94.3 },
      { rank: 7, title: "Dune II", score: 93.2 },
      { rank: 8, title: "X-COM: UFO Defense", score: 92.1 },
      { rank: 9, title: "Prince of Persia", score: 91.0 },
      { rank: 10, title: "Wolfenstein 3D", score: 89.8 },
      { rank: 11, title: "Day of the Tentacle", score: 88.7 },
      { rank: 12, title: "Fallout", score: 87.6 },
      { rank: 13, title: "Quake", score: 86.5 },
      { rank: 14, title: "Duke Nukem 3D", score: 85.3 },
      { rank: 15, title: "Fate of Atlantis", score: 84.2 },
      { rank: 16, title: "C&C: Red Alert", score: 83.1 },
      { rank: 17, title: "Warcraft II", score: 82.0 },
      { rank: 18, title: "Star Control II", score: 80.8 },
      { rank: 19, title: "Syndicate", score: 79.7 },
      { rank: 20, title: "Ultima VII", score: 78.6 },
      { rank: 21, title: "Alone in the Dark", score: 77.5 },
      { rank: 22, title: "Gabriel Knight", score: 76.3 },
      { rank: 23, title: "Heroes of Might and Magic II", score: 75.2 },
      { rank: 24, title: "Sam & Max Hit the Road", score: 74.1 },
      { rank: 25, title: "TIE Fighter", score: 73.0 },
      { rank: 26, title: "Lemmings", score: 71.8 },
      { rank: 27, title: "Descent", score: 70.7 },
      { rank: 28, title: "Wing Commander III", score: 69.6 },
      { rank: 29, title: "Dungeon Keeper", score: 68.5 },
      { rank: 30, title: "Full Throttle", score: 67.3 },
      { rank: 31, title: "Theme Hospital", score: 66.2 },
      { rank: 32, title: "Blood", score: 65.1 },
      { rank: 33, title: "Transport Tycoon Deluxe", score: 64.0 },
      { rank: 34, title: "MechWarrior 2", score: 62.8 },
      { rank: 35, title: "The Dig", score: 61.7 },
      { rank: 36, title: "Scorched Earth", score: 60.6 },
      { rank: 37, title: "Leisure Suit Larry", score: 59.5 },
      { rank: 38, title: "Zork I", score: 58.3 },
      { rank: 39, title: "Battle Chess", score: 57.2 },
      { rank: 40, title: "Little Big Adventure", score: 56.1 },
      { rank: 41, title: "Oregon Trail (Deluxe)", score: 55.0 },
      { rank: 42, title: "Jazz Jackrabbit", score: 53.8 },
      { rank: 43, title: "Heretic", score: 52.7 },
      { rank: 44, title: "Loom", score: 51.6 },
      { rank: 45, title: "Carmageddon", score: 50.5 },
      { rank: 46, title: "Eye of the Beholder", score: 49.3 },
      { rank: 47, title: "King's Quest V", score: 48.2 },
      { rank: 48, title: "Beneath a Steel Sky", score: 47.1 },
      { rank: 49, title: "Commander Keen 4", score: 46.0 },
      { rank: 50, title: "Tyrian", score: 44.8 }
    ],
    s2: [
      { rank: 1, title: "Doom", score: 99 },
      { rank: 2, title: "Quake", score: 98 },
      { rank: 3, title: "Wolfenstein 3D", score: 97 },
      { rank: 4, title: "Commander Keen", score: 96 },
      { rank: 5, title: "Warcraft II: Tides of Darkness", score: 96 },
      { rank: 6, title: "StarCraft", score: 95 },
      { rank: 7, title: "Dune II", score: 95 },
      { rank: 8, title: "Age of Empires", score: 94 },
      { rank: 9, title: "Prince of Persia", score: 93 },
      { rank: 10, title: "Ultima Underworld", score: 92 },
      { rank: 11, title: "Civilization", score: 92 },
      { rank: 12, title: "X-COM: UFO Defense", score: 91 },
      { rank: 13, title: "Descent", score: 90 },
      { rank: 14, title: "Tie Fighter", score: 90 },
      { rank: 15, title: "System Shock", score: 89 },
      { rank: 16, title: "Jazz Jackrabbit", score: 88 },
      { rank: 17, title: "Lemmings", score: 87 },
      { rank: 18, title: "The Secret of Monkey Island", score: 87 },
      { rank: 19, title: "Indiana Jones and the Fate of Atlantis", score: 86 },
      { rank: 20, title: "SimCity 2000", score: 85 },
      { rank: 21, title: "Heretic", score: 84 },
      { rank: 22, title: "Blood", score: 83 },
      { rank: 23, title: "Duke Nukem 3D", score: 83 },
      { rank: 24, title: "Wing Commander", score: 82 },
      { rank: 25, title: "Raptor: Call of the Shadows", score: 81 },
      { rank: 26, title: "Master of Orion II", score: 80 },
      { rank: 27, title: "Syndicate", score: 79 },
      { rank: 28, title: "Blake Stone: Aliens of Gold", score: 78 },
      { rank: 29, title: "Tyrian", score: 77 },
      { rank: 30, title: "Epic Pinball", score: 76 },
      { rank: 31, title: "Doom II", score: 76 },
      { rank: 32, title: "Hexen: Beyond Heretic", score: 75 },
      { rank: 33, title: "Warcraft: Orcs & Humans", score: 74 },
      { rank: 34, title: "Theme Hospital", score: 74 },
      { rank: 35, title: "Betrayal at Krondor", score: 73 },
      { rank: 36, title: "Alone in the Dark", score: 73 },
      { rank: 37, title: "Day of the Tentacle", score: 72 },
      { rank: 38, title: "Full Throttle", score: 71 },
      { rank: 39, title: "Ultima VII", score: 71 },
      { rank: 40, title: "Abuse", score: 70 },
      { rank: 41, title: "Lands of Lore", score: 69 },
      { rank: 42, title: "Sam & Max Hit the Road", score: 69 },
      { rank: 43, title: "Screamer", score: 68 },
      { rank: 44, title: "Terminal Velocity", score: 67 },
      { rank: 45, title: "Rise of the Triad", score: 67 },
      { rank: 46, title: "Star Wars: Dark Forces", score: 66 },
      { rank: 47, title: "Dangerous Dave", score: 65 },
      { rank: 48, title: "One Must Fall: 2097", score: 65 },
      { rank: 49, title: "Commander Blood", score: 64 },
      { rank: 50, title: "Prehistorik 2", score: 63 }
    ]
  }
};

let md = "# Top 50 games per console (union of the two lists)\n\n";
md +=
  "Simple union (S1 + S2) with de-dupe of obvious variants. Ordered by best (lowest) rank across sources, then best score.\n\n";

for (const key of ["NES", "SNES", "atari2600", "arcade", "msdos"]) {
  const cfg = data[key];
  const rows = mergeTop50(cfg);

  md += `## ${cfg.name}\n\n`;
  md += "| # | Title | Best score | S1 rank/score | S2 rank/score |\n|---:|---|---:|---|---|\n";
  rows.forEach((r, idx) => {
    const title = String(r.title).replace(/\|/g, "\\|");
    md += `| ${idx + 1} | ${title} | ${r.bestScore} | ${r.s1} | ${r.s2} |\n`;
  });
  md += "\n";
}

fs.writeFileSync(outPath, md);
console.log(`Wrote ${outPath}`);

