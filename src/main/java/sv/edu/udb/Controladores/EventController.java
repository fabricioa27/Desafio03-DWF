package sv.edu.udb.Controladores;

import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.security.SecurityRequirement;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import sv.edu.udb.Modelos.Event;
import sv.edu.udb.Servicios.EventService;
import sv.edu.udb.dto.request.EventRequestDto;
import sv.edu.udb.dto.response.EventResponseDto;

import java.util.List;
import java.util.stream.Collectors;

@RequiredArgsConstructor
@Tag(name = "Eventos", description = "Endpoints para la gestión de eventos")
@RestController
@RequestMapping("/api/events")
public class EventController {

    private final EventService eventService;

    @Operation(summary = "Listar todos los eventos (paginado)", description = "JWT requerido. Usa parámetros: page, size, sort")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @GetMapping
    public ResponseEntity<Page<EventResponseDto>> listarEventos(Pageable pageable) {
        Page<Event> events = eventService.listarEventosPaginados(pageable);
        Page<EventResponseDto> responseDtos = events.map(this::toResponseDto);
        return ResponseEntity.ok(responseDtos);
    }

    @Operation(summary = "Obtener evento por ID", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @GetMapping("/{id}")
    public ResponseEntity<EventResponseDto> obtenerEventoPorId(@PathVariable Integer id) {
        Event event = eventService.obtenerPorId(id);
        return ResponseEntity.ok(toResponseDto(event));
    }

    @Operation(summary = "Crear nuevo evento", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @PostMapping
    public ResponseEntity<EventResponseDto> crearEvento(@Valid @RequestBody EventRequestDto requestDto) {
        Event event = toEntity(requestDto);
        Event createdEvent = eventService.crearEvento(event);
        return ResponseEntity.status(HttpStatus.CREATED).body(toResponseDto(createdEvent));
    }

    @Operation(summary = "Actualizar datos de un evento", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @PutMapping("/{id}")
    public ResponseEntity<EventResponseDto> actualizarEvento(
            @PathVariable Integer id,
            @Valid @RequestBody EventRequestDto requestDto) {
        Event eventDetails = toEntity(requestDto);
        Event updatedEvent = eventService.actualizarEvento(id, eventDetails);
        return ResponseEntity.ok(toResponseDto(updatedEvent));
    }

    @Operation(summary = "Eliminar un evento", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminarEvento(@PathVariable Integer id) {
        eventService.eliminarEvento(id);
        return ResponseEntity.noContent().build();
    }

    private EventResponseDto toResponseDto(Event event) {
        return EventResponseDto.builder()
                .idEvent(event.getIdEvent())
                .title(event.getTitle())
                .description(event.getDescription())
                .eventDate(event.getEventDate())
                .venue(event.getVenue())
                .capacity(event.getCapacity())
                .pricePerTicket(event.getPricePerTicket())
                .build();
    }

    private Event toEntity(EventRequestDto dto) {
        Event event = new Event();
        event.setTitle(dto.getTitle());
        event.setDescription(dto.getDescription());
        event.setEventDate(dto.getEventDate());
        event.setVenue(dto.getVenue());
        event.setCapacity(dto.getCapacity());
        event.setPricePerTicket(dto.getPricePerTicket());
        return event;
    }
}
