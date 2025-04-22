<?php

declare(strict_types=1);

namespace App\Enums;

enum AppCategoryEnum: string
{
    case Business = 'public.app-category.business';
    case DeveloperTools = 'public.app-category.developer-tools';
    case Education = 'public.app-category.education';
    case Entertainment = 'public.app-category.entertainment';
    case Finance = 'public.app-category.finance';
    case Games = 'public.app-category.games';
    case GraphicsAndDesign = 'public.app-category.graphics-design';
    case HealthcareAndFitness = 'public.app-category.healthcare-fitness';
    case Lifestyle = 'public.app-category.lifestyle';
    case Medical = 'public.app-category.medical';
    case Music = 'public.app-category.music';
    case News = 'public.app-category.news';
    case Photography = 'public.app-category.photography';
    case Productivity = 'public.app-category.productivity';
    case Reference = 'public.app-category.reference';
    case SocialNetworking = 'public.app-category.social-networking';
    case Sports = 'public.app-category.sports';
    case Travel = 'public.app-category.travel';
    case Utilities = 'public.app-category.utilities';
    case Video = 'public.app-category.video';
    case Weather = 'public.app-category.weather';
    case ActionGames = 'public.app-category.action-games';
    case AdventureGames = 'public.app-category.adventure-games';
    case ArcadeGames = 'public.app-category.arcade-games';
    case BoardGames = 'public.app-category.board-games';
    case CardGames = 'public.app-category.card-games';
    case CasinoGames = 'public.app-category.casino-games';
    case DiceGames = 'public.app-category.dice-games';
    case EducationalGames = 'public.app-category.educational-games';
    case FamilyGames = 'public.app-category.family-games';
    case KidsGames = 'public.app-category.kids-games';
    case MusicGames = 'public.app-category.music-games';
    case PuzzleGames = 'public.app-category.puzzle-games';
    case RacingGames = 'public.app-category.racing-games';
    case RolePlayingGames = 'public.app-category.role-playing-games';
    case SimulationGames = 'public.app-category.simulation-games';
    case SportsGames = 'public.app-category.sports-games';
    case StrategyGames = 'public.app-category.strategy-games';
    case TriviaGames = 'public.app-category.trivia-games';
    case WordGames = 'public.app-category.word-games';

    public function label(): string
    {
        return __('app.'.$this->value);
    }
}
