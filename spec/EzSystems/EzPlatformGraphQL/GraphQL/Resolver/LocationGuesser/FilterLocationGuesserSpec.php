<?php

namespace spec\EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\FilterLocationGuesser;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationFilter;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuess;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationGuesser;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\ObjectStorageLocationList;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationList;
use EzSystems\EzPlatformGraphQL\GraphQL\Resolver\LocationGuesser\LocationProvider;
use PhpSpec\ObjectBehavior;

class FilterLocationGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FilterLocationGuesser::class);
        $this->shouldHaveType(LocationGuesser::class);
    }

    function let(LocationProvider $locationProvider, LocationFilter $locationFilter, LocationFilter $otherLocationFilter)
    {
        $this->beConstructedWith($locationProvider, [$locationFilter, $otherLocationFilter]);
    }

    function it_gets_the_initial_location_list_from_the_provider(LocationProvider $locationProvider)
    {
        $content = new Content();
        $locationProvider->getLocations($content)->willReturn(new ObjectStorageLocationList($content));
        $this->guessLocation($content);
    }

    function it_does_not_filter_if_there_is_only_one_location(LocationProvider $locationProvider, LocationFilter $locationFilter)
    {
        $content = new Content();
        $location = new Location();
        $locationList = new ObjectStorageLocationList($content);
        $locationList->addLocation($location);

        $locationProvider->getLocations($content)->willReturn($locationList);

        $locationFilter->filter($content, $locationList)->shouldNotBeCalled();
        $this->guessLocation($content)->shouldBeLike(new LocationGuess($content, [$location]));
    }

    function it_returns_as_soon_as_there_is_one_location_left(
        LocationProvider $locationProvider,
        LocationFilter $locationFilter,
        LocationFilter $secondLocationFilter,
        LocationList $locationList
    )
    {
        $content = new Content();
        $firstLocation = new Location();
        $secondLocation = new Location();

        $locationProvider->getLocations($content)->willReturn($locationList);
        $locationList->hasOneLocation()->willReturn(false);
        $locationList->getLocations()->willReturn([$firstLocation]);
        $locationFilter->filter($content, $locationList)->will(function ($args) use ($locationList) {
            $locationList->hasOneLocation()->willReturn(true);
        });

        $secondLocationFilter->filter($content, $locationList)->shouldNotBeCalled();

        $this->guessLocation($content)->shouldGuess($firstLocation);
    }

    public function getMatchers(): array
    {
        return [
            'guess' => function (LocationGuess $subject, Location $location) {
                return $subject->isSuccessful() && $subject->getLocation() === $location;
            }
        ];
    }
}
