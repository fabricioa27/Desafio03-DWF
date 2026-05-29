package sv.edu.udb.Controladores;

import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.security.SecurityRequirement;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.*;
import sv.edu.udb.Configuraciones.UserDetailsImpl;
import sv.edu.udb.Modelos.Booking;
import sv.edu.udb.Modelos.User;
import sv.edu.udb.Servicios.BookingService;
import sv.edu.udb.dto.request.BookingRequestDto;
import sv.edu.udb.dto.response.BookingResponseDto;

import java.util.List;
import java.util.stream.Collectors;

@RequiredArgsConstructor
//Anotacion para personalizar el swagger
@Tag(name = "Reservas", description = "Endpoints para la gestión de reservas")
@RestController
@RequestMapping("/api/bookings")
public class BookingController {

    private final BookingService bookingService;

    @Operation(summary = "Crear reserva (calcula total automáticamente)", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth") // Le indica al swagger que se requiere un esquema de seguridad especifico
    @PreAuthorize("isAuthenticated()") //Verifica en el ContextHolder si ese usuario esta autenticado
    @PostMapping
    public ResponseEntity<BookingResponseDto> crearReserva(
            @Valid @RequestBody BookingRequestDto requestDto,
            Authentication authentication) {
        User usuario = getAuthenticatedUser(authentication); //obtener los valores del usuario autenticado
        
        Booking booking = bookingService.crearReserva(requestDto, usuario);
        return ResponseEntity.status(HttpStatus.CREATED).body(toResponseDto(booking));
    }

    @Operation(summary = "Listar reservas del usuario autenticado", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @GetMapping("/my")
    public ResponseEntity<List<BookingResponseDto>> listarMisReservas(Authentication authentication) {
        User usuario = getAuthenticatedUser(authentication);
        
        List<Booking> bookings = bookingService.listarMisReservas(usuario);
        List<BookingResponseDto> responseDtos = bookings.stream()
                .map(this::toResponseDto)
                .collect(Collectors.toList());
        return ResponseEntity.ok(responseDtos);
    }

    @Operation(summary = "Cancelar una reserva (cambia status)", description = "JWT requerido")
    @SecurityRequirement(name = "bearerAuth")
    @PreAuthorize("isAuthenticated()")
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> cancelarReserva(@PathVariable Integer id) {
        bookingService.cancelarReserva(id);
        return ResponseEntity.noContent().build();
    }

    //Metodos auxiliares
    private BookingResponseDto toResponseDto(Booking booking) {
        return BookingResponseDto.builder()
                .idBooking(booking.getIdBooking())
                .eventId(booking.getEvent() != null ? booking.getEvent().getIdEvent() : null)
                .eventTitle(booking.getEvent() != null ? booking.getEvent().getTitle() : null)
                .userId(booking.getUser() != null ? booking.getUser().getIdUser() : null)
                .username(booking.getUser() != null ? booking.getUser().getUsername() : null)
                .quantity(booking.getQuantity())
                .totalAmount(booking.getTotalAmount())
                .bookingDate(booking.getBookingDate())
                .status(booking.getStatus() != null ? booking.getStatus().name() : null)
                .build();
    }

    private User getAuthenticatedUser(Authentication authentication) {
        UserDetailsImpl userDetails = (UserDetailsImpl) authentication.getPrincipal();
        return userDetails.getUser();
    }
}
