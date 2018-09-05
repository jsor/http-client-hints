<?php

namespace Jsor\HttpClientHints;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_resolves_headers()
    {
        $resolver = new Resolver();

        $params = $resolver->resolve(array(
            'DPR'            => '2',
            'Width'          => '123',
            'Viewport-Width' => array('1234'),
            'Downlink'       => '0.384',
            'Save-Data'      => 'on',
        ));

        $expected = array(
            'dpr'            => '2',
            'width'          => '123',
            'viewport-width' => '1234',
            'downlink'       => '0.384',
            'save-data'      => 'on',
        );

        $this->assertEquals($expected, $params);
    }

    /** @test */
    public function it_resolves_headers_from_server_global()
    {
        $resolver = new Resolver();

        $params = $resolver->resolve(array(
            'HTTP_DPR'            => '2',
            'HTTP_WIDTH'          => '123',
            'HTTP_VIEWPORT_WIDTH' => array('1234'),
            'HTTP_DOWNLINK'       => '0.384',
            'HTTP_SAVE_DATA'      => 'on',
            'HTTP_FOO'            => 'bar',
        ));

        $expected = array(
            'dpr'            => '2',
            'width'          => '123',
            'viewport-width' => '1234',
            'downlink'       => '0.384',
            'save-data'      => 'on',
        );

        $this->assertEquals($expected, $params);
    }

    /** @test */
    public function it_is_configurable_via_constructor()
    {
        $resolver = new Resolver(array(
            'mapping' => array(
                'width' => 'w',
                'dpr'   => 'device-pixel-ratio'
            ),
            'allowed_headers' => array(
                'width',
                'dpr',
            )
        ));

        $params = $resolver->resolve(array(
            'HTTP_WIDTH' => '123',
            'HTTP_DPR'   => '2',
            'HTTP_FOO'   => 'bar',
        ));

        $expected = array(
            'device-pixel-ratio' => '2',
            'w'                  => '123',
        );

        $this->assertEquals($expected, $params);
    }

    /** @test */
    public function it_is_configurable_via_methods()
    {
        $resolver = new Resolver();

        $resolver = $resolver
            ->withMapping(array(
                'Width' => 'w',
                'Dpr'   => 'device-pixel-ratio',
            ))
            ->withAllowedHeaders(array(
                'width',
                'dpr',
            ))
        ;

        $params = $resolver->resolve(array(
            'HTTP_WIDTH' => '123',
            'HTTP_DPR'   => '2',
            'HTTP_FOO'   => 'bar',
        ));

        $expected = array(
            'device-pixel-ratio' => '2',
            'w'                  => '123',
        );

        $this->assertEquals($expected, $params);
    }

    /** @test */
    public function it_supports_allowed_headers_as_string()
    {
        $resolver = new Resolver();

        $resolver = $resolver
            ->withAllowedHeaders('Width,DPR,FOO')
        ;

        $params = $resolver->resolve(array(
            'HTTP_WIDTH' => '123',
            'HTTP_DPR'   => '2',
        ));

        $expected = array(
            'dpr'   => '2',
            'width' => '123',
        );

        $this->assertEquals($expected, $params);
    }

    /** @test */
    public function it_recalculates_height()
    {
        $resolver = new Resolver();

        $resolver = $resolver
            ->withAllowedHeaders('Width,DPR,FOO')
        ;

        $params = $resolver->resolve(
            array(
                'HTTP_WIDTH' => '200',
            ),
            array(
                'width'  => '400',
                'height' => '300'
            )
        );

        $expected = array(
            'width'  => '200',
            'height' => '150',
        );

        $this->assertEquals($expected, $params);
    }
}
