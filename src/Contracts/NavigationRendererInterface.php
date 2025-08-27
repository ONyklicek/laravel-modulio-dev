<?php

namespace NyonCode\LaravelModulio\Contracts;

use Illuminate\Support\Collection;
use NyonCode\LaravelModulio\Navigation\NavigationGroup;
use NyonCode\LaravelModulio\Navigation\NavigationItem;

/**
 * Kontrakt pro navigation renderer
 *
 * Definuje rozhraní pro vykreslování navigačních menu
 * s podporou různých stylů a formátů.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Navigation Renderer Contract
 *
 * Defines interface for rendering navigation menus
 * with support for different styles and formats.
 */
interface NavigationRendererInterface
{
    /**
     * Vykreslí navigaci
     * Renders navigation
     *
     * @param string $menuName Název menu / Menu name
     * @param array $options Možnosti vykreslení / Rendering options
     * @return string HTML výstup / HTML output
     */
    public function render(string $menuName, array $options = []): string;

    /**
     * Vykreslí hierarchickou navigaci
     * Renders hierarchical navigation
     *
     * @param Collection $items Navigační položky / Navigation items
     * @param array $options Možnosti vykreslení / Rendering options
     * @return string HTML výstup / HTML output
     */
    public function renderHierarchical(Collection $items, array $options = []): string;

    /**
     * Vykreslí položku navigace
     * Renders navigation item
     *
     * @param NavigationItem $item
     * @param array $options Možnosti vykreslení / Rendering options
     * @return string HTML výstup / HTML output
     */
    public function renderItem(NavigationItem $item, array $options = []): string;

    /**
     * Vykreslí skupinu navigace
     * Renders navigation group
     *
     * @param NavigationGroup $group
     * @param array $options Možnosti vykreslení / Rendering options
     * @return string HTML výstup / HTML output
     */
    public function renderGroup(NavigationGroup $group, array $options = []): string;

    /**
     * Nastaví template pro vykreslování
     * Sets rendering template
     *
     * @param string $template Název template / Template name
     * @return self
     */
    public function setTemplate(string $template): self;

    /**
     * Vrací dostupné templates
     * Returns available templates
     *
     * @return array<string>
     */
    public function getAvailableTemplates(): array;
}